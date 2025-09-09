#!/usr/bin/env bash
set -Eeuo pipefail

COMPOSE="docker compose -f docker-compose.secure.yml"
APP="birentable"

DO_SEED="no"
DO_FRESH="no"

# Parseo simple de flags
for arg in "$@"; do
  case "$arg" in
    --seed)  DO_SEED="yes" ;;
    --fresh) DO_FRESH="yes" ;;
    *)
      echo "Uso: $0 [--seed] [--fresh]" >&2
      exit 2
      ;;
  esac
done

log()  { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✓ %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m! %s\033[0m\n' "$*"; }

# 1) (Opcional) Build frontend si existe package.json
if [[ -f package.json ]]; then
  log "Instalando dependencias Node y compilando Vite (contenedores efímeros)…"
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm ci
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm run build
  ok "Assets compilados."
else
  warn "No hay package.json; omito build de frontend."
fi

# 2) Build imagen de la app (composer corre dentro del Dockerfile)
log "Construyendo imagen de la app…"
$COMPOSE build app

# 3) Poblar el volumen app_code desde la imagen
log "Poblando volumen app_code…"
$COMPOSE run --rm code_init

# 4) Levantar servicios
log "Levantando stack…"
$COMPOSE up -d
$COMPOSE ps
ok "Stack arriba."

# 5) .env y APP_KEY
log "Asegurando .env…"
$COMPOSE exec "$APP" sh -lc 'test -f .env || cp .env.example .env'
log "Generando APP_KEY (si falta)…"
$COMPOSE exec "$APP" php artisan key:generate --force || true

# 6) Migraciones / seed
if [[ "$DO_FRESH" == "yes" ]]; then
  warn "Ejecutando migrate:fresh --seed (ELIMINA datos)…"
  $COMPOSE exec "$APP" php artisan migrate:fresh --seed --force
else
  log "Ejecutando migraciones…"
  $COMPOSE exec "$APP" php artisan migrate --force
  if [[ "$DO_SEED" == "yes" ]]; then
    log "Ejecutando seeders…"
    $COMPOSE exec "$APP" php artisan db:seed --force
  fi
fi

# 7) storage:link y optimizaciones
log "storage:link + optimize…"
$COMPOSE exec "$APP" php artisan storage:link || true
$COMPOSE exec "$APP" php artisan config:clear || true
$COMPOSE exec "$APP" php artisan route:clear || true
$COMPOSE exec "$APP" php artisan view:clear || true
$COMPOSE exec "$APP" php artisan optimize

ok "Deploy completado. Visita https://portal.birentable.com"
