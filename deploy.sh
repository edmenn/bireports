#!/usr/bin/env bash
set -Eeuo pipefail

COMPOSE="docker compose -f docker-compose.secure.yml"
APP="app"             # << nombre del SERVICIO (no el container_name)
DOMAIN="${DOMAIN:-portal.birentable.com}"

log(){  printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok(){   printf '\033[1;32m✓ %s\033[0m\n' "$*"; }
warn(){ printf '\033[1;33m! %s\033[0m\n' "$*"; }

# 0) Build frontend si existe package.json (opcional)
if [[ -f package.json ]]; then
  log "Instalando dependencias Node y compilando Vite…"
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm ci
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm run build
  ok "Assets compilados."
else
  warn "No hay package.json; omito build de frontend."
fi

# 1) Construir imagen de la app (Composer corre en el Dockerfile)
log "Construyendo imagen de la app…"
$COMPOSE build app

# 2) Poblar el volumen con el código de la imagen
log "Poblando volumen app_code…"
$COMPOSE run --rm code_init

# 3) Copiar .env del host al volumen (si existe en el repo)
log "Copiando .env del host al volumen (si existe)…"
$COMPOSE run --rm env_init || true

# 4) Levantar servicios principales
log "Levantando stack…"
$COMPOSE up -d pgsql app web caddy
$COMPOSE ps
ok "Servicios arriba."

# 5) Asegurar .env dentro del contenedor (si no copiamos uno)
log "Asegurando .env dentro del contenedor…"
$COMPOSE exec "$APP" sh -lc 'test -f .env || cp .env.example .env'

# 6) APP_KEY + migraciones + enlaces + optimización
log "Generando APP_KEY (si falta)…"
$COMPOSE exec "$APP" php artisan key:generate --force || true

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
