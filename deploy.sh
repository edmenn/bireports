#!/usr/bin/env bash
set -euo pipefail

# ================================
# deploy.sh — despliegue producción
# Stack: Caddy + Nginx + PHP-FPM + PostgreSQL
# - Composer install (contenedor efímero)
# - NPM build (contenedor efímero)
# - docker compose up -d --build
# - Artisan: key/migrate/storage:link/optimize
#
# Uso:
#   bash deploy.sh            # migraciones normales
#   bash deploy.sh --seed     # migraciones + seeders
#   bash deploy.sh --fresh    # migrate:fresh --seed (BORRA datos)
# ================================

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.secure.yml}"
APP_SERVICE="${APP_SERVICE:-birentable}"   # nombre del servicio PHP-FPM en tu compose

DO_SEED="no"
DO_FRESH="no"

for arg in "${@:-}"; do
  case "$arg" in
    --seed)  DO_SEED="yes" ;;
    --fresh) DO_FRESH="yes" ;;
    *)
      echo "Uso: bash deploy.sh [--seed] [--fresh]" >&2
      exit 2
      ;;
  esac
done

log() { printf "\033[1;34m==> %s\033[0m\n" "$*"; }
ok()  { printf "\033[1;32m✓ %s\033[0m\n" "$*"; }
warn(){ printf "\033[1;33m! %s\033[0m\n" "$*"; }
err() { printf "\033[1;31m✗ %s\033[0m\n" "$*"; }

# -------- Validaciones básicas --------
command -v docker >/dev/null || { err "Docker no está instalado/en PATH"; exit 1; }
[[ -f "$COMPOSE_FILE" ]] || { err "No existe $COMPOSE_FILE en el directorio actual"; exit 1; }

# -------- 1) Composer install (prod) --------
log "Instalando dependencias PHP (composer install)…"
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$PWD:/app" -w /app \
  composer:2 install --no-dev --optimize-autoloader --no-interaction
ok "Composer listo."

# -------- 2) Build assets (Vite) --------
if [[ -f package.json ]]; then
  log "Instalando dependencias Node (npm ci)…"
  docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$PWD:/app" -w /app \
    node:20 npm ci

  log "Compilando assets de producción (npm run build)…"
  docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$PWD:/app" -w /app \
    node:20 npm run build
  ok "Assets compilados."
else
  warn "No hay package.json; omito build de frontend."
fi

# -------- 3) Levantar/actualizar contenedores --------
log "Levantando stack con $COMPOSE_FILE (build + up -d)…"
docker compose -f "$COMPOSE_FILE" up -d --build
ok "Contenedores arriba."
docker compose -f "$COMPOSE_FILE" ps

# -------- 4) .env y APP_KEY --------
log "Asegurando .env dentro del contenedor ($APP_SERVICE)…"
if ! docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" test -f .env; then
  docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" cp .env.example .env
  ok ".env creado desde .env.example"
fi

log "Generando APP_KEY si falta…"
docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan key:generate --force || true
ok "APP_KEY verificado."

# -------- 5) Migraciones / seed --------
if [[ "$DO_FRESH" == "yes" ]]; then
  warn "Ejecutando migrate:fresh --seed (ELIMINA datos)…"
  docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan migrate:fresh --seed --force
  ok "migrate:fresh --seed ok."
else
  log "Ejecutando migraciones…"
  docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan migrate --force
  ok "Migraciones ok."
  if [[ "$DO_SEED" == "yes" ]]; then
    log "Ejecutando seeders…"
    docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan db:seed --force
    ok "Seeders ok."
  fi
fi

# -------- 6) storage:link y optimizaciones --------
log "Creando storage:link (idempotente)…"
docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan storage:link || true

log "Limpiando caches y optimizando…"
docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan config:clear || true
docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan route:clear || true
docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan view:clear || true
docker compose -f "$COMPOSE_FILE" exec "$APP_SERVICE" php artisan optimize
ok "Optimización lista."

echo
ok "Deploy completado. Verifica tu sitio en producción."
