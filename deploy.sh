#!/usr/bin/env bash
set -Eeuo pipefail

COMPOSE="docker compose -f docker-compose.secure.yml"
APP="app"             # nombre del servicio (no el container_name)
DOMAIN="${DOMAIN:-portal.birentable.com}"

log(){  printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok(){   printf '\033[1;32m✓ %s\033[0m\n' "$*"; }
warn(){ printf '\033[1;33m! %s\033[0m\n' "$*"; }

# 0) Asegurar que existe un .env en el host
if [[ ! -f .env ]]; then
  warn "No existe .env en el host. Creando base mínima…"
  cat > .env <<'ENVEOF'
APP_NAME=Birentable
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://portal.birentable.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES
APP_MAINTENANCE_DRIVER=file

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=birentabledb
DB_USERNAME=Hq7vug
DB_PASSWORD=J8f8Wgy1uG3TO8dZo5aT

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.birentable.com
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@birentable.com
MAIL_FROM_NAME="${APP_NAME}"

APP_TIMEZONE=America/Asuncion
AUTH_ALLOW_LOCAL_LOGIN=false
POWERBI_SCOPE=https://analysis.windows.net/powerbi/api/.default

TRUSTED_PROXIES=*
TRUSTED_HOSTS=portal.birentable.com
ENVEOF
  ok ".env base creado."
fi

# 0.1) Generar APP_KEY en el host si falta
if ! grep -q '^APP_KEY=' .env || grep -q '^APP_KEY=\s*$' .env; then
  warn "APP_KEY vacío. Generando uno nuevo…"
  NEWKEY="base64:$(openssl rand -base64 32)"
  # reemplaza línea APP_KEY=... (o la agrega si no existe)
  if grep -q '^APP_KEY=' .env; then
    sed -i "s|^APP_KEY=.*$|APP_KEY=${NEWKEY}|" .env
  else
    printf "\nAPP_KEY=%s\n" "$NEWKEY" >> .env
  fi
  ok "APP_KEY generado en .env del host."
fi

# 1) (Opcional) Build frontend si existe package.json
if [[ -f package.json ]]; then
  log "Instalando dependencias Node y compilando Vite…"
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm ci
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm run build
  ok "Assets compilados."
else
  warn "No hay package.json; omito build de frontend."
fi

# 2) Build imagen de la app (Composer corre dentro del Dockerfile)
log "Construyendo imagen de la app…"
$COMPOSE build app

# 3) Poblar el volumen con el código de la imagen
log "Poblando volumen app_code…"
$COMPOSE run --rm code_init

# 4) Levantar servicios principales
log "Levantando stack…"
$COMPOSE up -d pgsql app web caddy
$COMPOSE ps
ok "Servicios arriba."

# 5) Migraciones + enlaces + optimización (APP_KEY ya viene del host)
log "Creando tablas base (sessions/queue/cache) si faltan…"
$COMPOSE exec "$APP" php artisan session:table || true
$COMPOSE exec "$APP" php artisan queue:table   || true
$COMPOSE exec "$APP" php artisan cache:table   || true

log "Ejecutando migraciones…"
$COMPOSE exec "$APP" php artisan migrate --force

log "storage:link + optimize…"
$COMPOSE exec "$APP" php artisan storage:link || true
$COMPOSE exec "$APP" php artisan config:clear || true
$COMPOSE exec "$APP" php artisan route:clear  || true
$COMPOSE exec "$APP" php artisan view:clear   || true
$COMPOSE exec "$APP" php artisan optimize

ok "Deploy completado. Revisa https://$DOMAIN"
