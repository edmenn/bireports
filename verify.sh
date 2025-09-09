#!/usr/bin/env bash
set -Eeuo pipefail

COMPOSE="docker compose -f docker-compose.secure.yml"
APP_SERVICE="app"
DOMAIN="${DOMAIN:-portal.birentable.com}"

log() { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok()  { printf '\033[1;32mOK %s\033[0m\n' "$*"; }
warn(){ printf '\033[1;33m! %s\033[0m\n' "$*"; }
err() { printf '\033[1;31mX %s\033[0m\n' "$*"; }

# 0) Archivos minimos
for f in docker-compose.secure.yml Dockerfile.app Caddyfile nginx/default.conf; do
  [[ -f "$f" ]] || { err "Falta $f"; exit 1; }
done
ok "Infra OK: archivos presentes."

# 1) Stack arriba
log "Verificando contenedores (caddy, web, app, pgsql)."
$COMPOSE ps
for s in caddy web "$APP_SERVICE" pgsql; do
  if ! $COMPOSE ps --status running --services | grep -qx "$s"; then
    warn "Servicio $s no está 'running'. Intento levantar."
    $COMPOSE up -d "$s"
  fi
done
ok "Servicios levantados."

# 2) .env efectivo / APP_KEY
log ".env/APP_KEY dentro del contenedor (solo lectura del entorno)."
if $COMPOSE exec -T "$APP_SERVICE" sh -lc 'v=$(printenv APP_KEY || true); [ -n "$v" ] && [ "$v" != "base64:" ]'; then
  ok "APP_KEY presente en el entorno del contenedor."
else
  warn "APP_KEY vacio/no presente. Define APP_KEY en el host (.env) y recrea los servicios."
  echo "  Ejemplo: KEY=\$(openssl rand -base64 32); sed -i -E \"s#^APP_KEY=.*#APP_KEY=base64:${KEY}\" .env"
  echo "           docker compose -f docker-compose.secure.yml up -d"
fi

# 3) Permisos y rutas de escritura
log "Probando escritura en storage/ y bootstrap/cache."
$COMPOSE exec -T "$APP_SERVICE" sh -lc 'mkdir -p storage/framework/{cache,views,sessions} storage/logs && echo test > storage/framework/cache/verify.txt && rm -f storage/framework/cache/verify.txt'
$COMPOSE exec -T "$APP_SERVICE" sh -lc 'echo test > bootstrap/cache/verify.txt && rm -f bootstrap/cache/verify.txt'
ok "Escritura OK en storage/ y bootstrap/cache."

# 4) Verificar extensiones PHP requeridas
log "Verificando extensiones PHP (mbstring, intl) y funcion mb_split."
if $COMPOSE exec -T "$APP_SERVICE" sh -lc 'php -m | grep -qi mbstring && php -m | grep -qi intl && php -r "exit(function_exists(\"mb_split\")?0:1);"'; then
  ok "Extensiones OK y mb_split presente."
else
  err "Faltan extensiones PHP (mbstring/intl) o mb_split. Reconstruye la imagen de app."
  echo "  docker compose -f docker-compose.secure.yml build --no-cache app && docker compose -f docker-compose.secure.yml up -d"
fi

# 5) Tablas base y migraciones (idempotente)
log "Asegurando tablas para session/queue/cache (idempotente)."
$COMPOSE exec -T "$APP_SERVICE" php artisan session:table || true
$COMPOSE exec -T "$APP_SERVICE" php artisan queue:table   || true
$COMPOSE exec -T "$APP_SERVICE" php artisan cache:table   || true
$COMPOSE exec -T "$APP_SERVICE" php artisan migrate --force
ok "Migraciones listas."

# 6) storage:link y optimizaciones
log "Creando storage:link y limpiando caches."
$COMPOSE exec -T "$APP_SERVICE" php artisan storage:link || true
$COMPOSE exec -T "$APP_SERVICE" php artisan config:clear || true
$COMPOSE exec -T "$APP_SERVICE" php artisan route:clear  || true
$COMPOSE exec -T "$APP_SERVICE" php artisan view:clear   || true
$COMPOSE exec -T "$APP_SERVICE" php artisan optimize
ok "Optimizacion aplicada."

# 7) Salud de servicios
log "Chequeando salud de servicios."
for s in caddy web "$APP_SERVICE" pgsql; do
  $COMPOSE ps "$s" | sed -n '2p' | awk '{print "  ", $1, $4, $5, $6, $7}'
done

# 8) Verificación HTTP (opcional)
log "Intentando acceder a https://$DOMAIN (puede tardar si TLS aun emite)."
set +e
HTTP_CODE=$(curl -k -sS -o /tmp/.curl_out -w '%{http_code}' "https://$DOMAIN" || true)
set -e
if [[ "$HTTP_CODE" =~ ^2|3 ]]; then
  ok "Sitio responde ($HTTP_CODE)."
else
  warn "HTTP=$HTTP_CODE. Causas tipicas: DNS sin propagar, puertos 80/443 bloqueados o error de app."
  warn "Logs utiles:"
  echo "  docker logs -f caddy"
  echo "  docker logs -f nginx"
  echo "  docker logs -f $APP"
fi

echo
ok "Verificacion completa."
