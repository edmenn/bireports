#!/usr/bin/env bash
set -Eeuo pipefail

COMPOSE="docker compose -f docker-compose.secure.yml"
APP="birentable"
DOMAIN="${DOMAIN:-portal.birentable.com}"

log(){ printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok(){  printf '\033[1;32m✓ %s\033[0m\n' "$*"; }
warn(){printf '\033[1;33m! %s\033[0m\n' "$*"; }
err(){ printf '\033[1;31m✗ %s\033[0m\n' "$*"; }

# 0) Archivos mínimos
for f in docker-compose.secure.yml Dockerfile.app Caddyfile nginx/default.conf; do
  [[ -f "$f" ]] || { err "Falta $f"; exit 1; }
done
ok "Archivos de infraestructura presentes."

# 1) Stack arriba
log "Verificando contenedores (caddy, nginx, birentable, pgsql)…"
$COMPOSE ps
for s in caddy nginx "$APP" pgsql; do
  if ! $COMPOSE ps --status running --services | grep -qx "$s"; then
    warn "Servicio $s no está 'running'. Intento levantar…"
    $COMPOSE up -d "$s"
  fi
done
ok "Servicios levantados."

# 2) .env disponible
log "Comprobando .env dentro del contenedor…"
$COMPOSE exec -T "$APP" sh -lc 'test -f .env || cp .env.example .env'
$COMPOSE exec -T "$APP" sh -lc 'grep -q "^APP_KEY=" .env || php artisan key:generate --force || true'
ok ".env listo y APP_KEY verificado."

# 3) Permisos y rutas de escritura
log "Probando escritura en storage/ y bootstrap/cache…"
$COMPOSE exec -T "$APP" sh -lc 'echo test > storage/framework/cache/verify.txt && rm -f storage/framework/cache/verify.txt'
$COMPOSE exec -T "$APP" sh -lc 'echo test > bootstrap/cache/verify.txt && rm -f bootstrap/cache/verify.txt'
ok "Escritura OK en storage/ y bootstrap/cache."

# 4) Tablas de soporte (idempotente)
log "Asegurando tablas para session/queue/cache (idempotente)…"
$COMPOSE exec -T "$APP" php artisan session:table || true
$COMPOSE exec -T "$APP" php artisan queue:table   || true
$COMPOSE exec -T "$APP" php artisan cache:table   || true
$COMPOSE exec -T "$APP" php artisan migrate --force
ok "Migraciones listas."

# 5) storage:link y optimizaciones
log "Creando storage:link y limpiando cachés…"
$COMPOSE exec -T "$APP" php artisan storage:link || true
$COMPOSE exec -T "$APP" php artisan config:clear || true
$COMPOSE exec -T "$APP" php artisan route:clear  || true
$COMPOSE exec -T "$APP" php artisan view:clear   || true
$COMPOSE exec -T "$APP" php artisan optimize
ok "Optimización aplicada."

# 6) Salud de servicios
log "Chequeando salud de servicios…"
for s in caddy nginx "$APP" pgsql; do
  $COMPOSE ps "$s" | sed -n '2p' | awk '{print "  •", $1, $4, $5, $6, $7}'
done

# 7) Verificación HTTP (opcional: requiere DNS apuntado)
log "Intentando acceder a https://$DOMAIN (puede tardar si el TLS aún emite)…"
set +e
HTTP_CODE=$(curl -k -sS -o /tmp/.curl_out -w '%{http_code}' "https://$DOMAIN" || true)
set -e
if [[ "$HTTP_CODE" =~ ^2|3 ]]; then
  ok "Sitio responde ($HTTP_CODE)."
else
  warn "HTTP=$HTTP_CODE. Las causas típicas: DNS sin propagar, puertos 80/443 bloqueados o error de app."
  warn "Mira logs si es necesario:"
  echo "  docker logs -f caddy"
  echo "  docker logs -f nginx"
  echo "  docker logs -f $APP"
fi

echo
ok "Verificación completa."
