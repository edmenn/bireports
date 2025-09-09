#!/usr/bin/env bash
set -Eeuo pipefail

# ---------- Config ----------
COMPOSE="docker compose -f docker-compose.secure.yml"
APP_SERVICE="app"          # nombre del servicio (clave en compose)
DB_SERVICE="pgsql"         # servicio de base de datos
DOMAIN="${DOMAIN:-portal.birentable.com}"

# ---------- Helpers ----------
log()  { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✓ %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m! %s\033[0m\n' "$*"; }
err()  { printf '\033[1;31m✗ %s\033[0m\n' "$*"; }

usage() {
  echo "Uso: $0 [--fresh] [--seed]"
  echo "  --fresh  = php artisan migrate:fresh --seed --force (BORRA datos)"
  echo "  --seed   = php artisan db:seed --force (tras migración normal)"
}

DO_FRESH="no"
DO_SEED="no"

for arg in "$@"; do
  case "$arg" in
    --fresh) DO_FRESH="yes" ;;
    --seed)  DO_SEED="yes"  ;;
    -h|--help) usage; exit 0 ;;
    *) usage; exit 2 ;;
  esac
done

require_file() {
  [[ -f "$1" ]] || { err "Falta $1"; exit 1; }
}

check_env_permissions() {
  local perm
  perm=$(stat -c "%a" .env 2>/dev/null || echo "???")
  if [[ "$perm" != "600" && "$perm" != "640" && "$perm" != "660" ]]; then
    warn ".env tiene permisos $perm (recomendado 600/640/660)."
    warn "Sugerencia: chmod 640 .env"
  fi
}

wait_healthy() {
  # $1 = service name, $2 = timeout seconds
  local svc="$1" timeout="${2:-120}"
  log "Esperando a que '$svc' esté healthy (timeout ${timeout}s)…"
  local start now
  start=$(date +%s)
  while true; do
    # docker compose ps --format json no está en todas las versiones; usamos grep
    if $COMPOSE ps "$svc" 2>/dev/null | grep -qiE 'healthy'; then
      ok "Servicio '$svc' healthy."
      return 0
    fi
    now=$(date +%s)
    if (( now - start > timeout )); then
      $COMPOSE ps "$svc" || true
      err "Timeout esperando health de '$svc'. Revisa logs."
      exit 1
    fi
    sleep 2
  done
}

# ---------- Prechequeos ----------
command -v docker >/dev/null || { err "Docker no está instalado/en PATH"; exit 1; }
require_file "docker-compose.secure.yml"
require_file ".env"
check_env_permissions

# ---------- Build frontend (si existe) ----------
if [[ -f package.json ]]; then
  log "Compilando assets (Node 20)…"
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm ci
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm run build
  ok "Assets listos."
else
  log "Sin package.json, omito build de frontend."
fi

# ---------- Build imagen app ----------
log "Construyendo imagen de la app…"
$COMPOSE build app
ok "Imagen construida."

# ---------- Poblar volumen de código desde imagen ----------
log "Poblando volumen 'app_code' (code_init)…"
$COMPOSE run --rm code_init
ok "Volumen 'app_code' poblado."

# ---------- Levantar servicios ----------
log "Levantando servicios: ${DB_SERVICE}, ${APP_SERVICE}, web, caddy…"
$COMPOSE up -d "$DB_SERVICE" "$APP_SERVICE" web caddy
$COMPOSE ps

# ---------- Esperar health de DB y APP ----------
wait_healthy "$DB_SERVICE" 120
# La app puede tardar un poco en levantar FPM con vendors y cache
wait_healthy "$APP_SERVICE" 120

# ---------- Comandos artisan ----------
if [[ "$DO_FRESH" == "yes" ]]; then
  warn "Ejecutando migrate:fresh --seed (¡BORRA DATOS!)…"
  $COMPOSE exec "$APP_SERVICE" php artisan migrate:fresh --seed --force
  ok "migrate:fresh --seed OK."
else
  log "Ejecutando migraciones…"
  $COMPOSE exec "$APP_SERVICE" php artisan migrate --force
  ok "Migraciones OK."
  if [[ "$DO_SEED" == "yes" ]]; then
    log "Ejecutando seeders…"
    $COMPOSE exec "$APP_SERVICE" php artisan db:seed --force
    ok "Seeders OK."
  fi
fi

log "storage:link + optimizaciones…"
$COMPOSE exec "$APP_SERVICE" php artisan storage:link || true
$COMPOSE exec "$APP_SERVICE" php artisan config:clear || true
$COMPOSE exec "$APP_SERVICE" php artisan route:clear  || true
$COMPOSE exec "$APP_SERVICE" php artisan view:clear   || true
$COMPOSE exec "$APP_SERVICE" php artisan optimize
ok "Optimización OK."

echo
ok "Deploy completado. Revisa https://${DOMAIN}"
