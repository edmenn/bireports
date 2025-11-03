#!/usr/bin/env bash
set -Eeuo pipefail

# ---------- Config ----------
COMPOSE="docker compose -f docker-compose.secure.yml"
APP_SERVICE="app"
DB_SERVICE="pgsql"
SCHEDULER_SERVICE="scheduler"
QUEUE_SERVICE="queue_worker"
DOMAIN="${DOMAIN:-portal.birentable.com}"

# ---------- Helpers ----------
log()  { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✓ %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m! %s\033[0m\n' "$*"; }
err()  { printf '\033[1;31m!! %s\033[0m\n' "$*"; }

ensure_zip_extension() {
  log "Verificando extensión ZipArchive..."
  if ! $COMPOSE exec -T "$APP_SERVICE" php -r "exit(extension_loaded('zip') ? 0 : 1);"; then
    err "ZipArchive no está habilitada en la imagen. Revisa Dockerfile.app."
    exit 1
  fi
  ok "ZipArchive disponible."
}

usage() {
  echo "Uso: $0 [--fresh] [--seed] [--refresh] [--refresh-nobuild]"
  echo "  --sync           = Sincroniza solo archivos (rsync) + reinicia app/web + migraciones"
  echo "  (sin argumentos) = modo interactivo"
  echo "  --fresh           = php artisan migrate:fresh --seed --force (BORRA datos)"
  echo "  --seed            = php artisan db:seed --force"
  echo "  --refresh         = Reconstruye imagen, repuebla app_code, recrea contenedores y limpia cachés"
  echo "  --refresh-nobuild = Reinicia contenedores existentes y limpia cachés (rápido)"
}

# ---------- Flags ----------
DO_FRESH="no"
DO_SEED="no"
DO_REFRESH="no"
DO_REFRESH_NOBUILD="no"
DO_SYNC_ONLY="no"

for arg in "$@"; do
  case "$arg" in
    --fresh) DO_FRESH="yes" ;;
    --seed)  DO_SEED="yes" ;;
    --refresh) DO_REFRESH="yes" ;;
    --refresh-nobuild) DO_REFRESH_NOBUILD="yes" ;;
    --sync) DO_SYNC_ONLY="yes" ;;
    -h|--help) usage; exit 0 ;;
    *) usage; exit 2 ;;
  esac
done

if [[ "$DO_SYNC_ONLY" == "yes" ]]; then
  if [[ "$DO_REFRESH" == "yes" || "$DO_REFRESH_NOBUILD" == "yes" || "$DO_FRESH" == "yes" ]]; then
    err "--sync no se puede combinar con --refresh/--refresh-nobuild/--fresh"
    exit 2
  fi
fi

# ---------- Menu interactivo ----------
if [[ $# -eq 0 && -t 0 ]]; then
  echo "Selecciona una acción:"
  echo "  1) Deploy normal (build + migrar)"
  echo "  2) Refresh (build + repoblar + limpiar caches)"
  echo "  3) Refresh rápido (sin build, limpiar caches/opcache)"
  echo "  4) Fresh (migrate:fresh --seed --force)"
  echo "  5) Solo seed (db:seed --force)"
  echo "  6) Sync rápido (solo archivos + migrar)"
  echo "  0) Salir"
  read -r -p "Opción [1/2/3/4/5/6/0]: " opt
  case "$opt" in
    1|"") ;;
    2) DO_REFRESH="yes" ;;
    3) DO_REFRESH_NOBUILD="yes" ;;
    4) DO_FRESH="yes" ;;
    5) DO_SEED="yes" ;;
    6) DO_SYNC_ONLY="yes" ;;
    0) exit 0 ;;
    *) echo "Opción inválida"; exit 2 ;;
  esac
fi

# ---------- Funciones auxiliares ----------
require_file() { [[ -f "$1" ]] || { err "Falta $1"; exit 1; }; }

check_env_permissions() {
  local perm
  perm=$(stat -c "%a" .env 2>/dev/null || echo "???")
  if [[ "$perm" != "600" && "$perm" != "640" && "$perm" != "660" ]]; then
    warn ".env tiene permisos $perm (recomendado 600/640/660)."
    warn "Sugerencia: chmod 640 .env"
  fi
}

ensure_storage_dirs() {
  log "Asegurando storage/* y bootstrap/cache dentro del contenedor."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc '
    set -e
    mkdir -p storage/framework/{cache,views,sessions} storage/logs &&
    chmod -R 775 storage bootstrap/cache &&
    chown -R 1000:1000 storage bootstrap/cache
  '
  ok "Directorios listos."
}

wait_healthy() {
  local svc="$1" timeout="${2:-120}"
  log "Esperando a que '$svc' esté healthy (timeout ${timeout}s)…"
  local start=$(date +%s)
  while true; do
    if $COMPOSE ps "$svc" 2>/dev/null | grep -qiE 'healthy'; then
      ok "Servicio '$svc' healthy."
      return 0
    fi
    local now=$(date +%s)
    if (( now - start > timeout )); then
      $COMPOSE ps "$svc" || true
      err "Timeout esperando health de '$svc'. Revisa logs."
      exit 1
    fi
    sleep 2
  done
}

ensure_app_key() {
  log "Verificando APP_KEY en .env"
  if grep -qE '^APP_KEY=' .env; then
    local cur
    cur=$(grep -E '^APP_KEY=' .env | sed 's/^APP_KEY=//')
    if [[ -n "$cur" && "$cur" != "base64:" ]]; then
      ok "APP_KEY ya presente."
      return 0
    fi
    warn "APP_KEY vacío. Generando uno nuevo."
  else
    warn "No existe línea APP_KEY en .env. Se creará."
  fi

  local key
  if command -v openssl >/dev/null 2>&1; then
    key=$(openssl rand -base64 32 | tr -d '\n')
  else
    key=$(head -c 32 /dev/urandom | base64 | tr -d '\n')
  fi
  sed -i -E "s#^APP_KEY=.*#APP_KEY=base64:${key}#" .env 2>/dev/null || echo "APP_KEY=base64:${key}" >> .env
  ok "APP_KEY generado."
}

# ---------- Prechequeos ----------
command -v docker >/dev/null || { err "Docker no está instalado/en PATH"; exit 1; }
require_file "docker-compose.secure.yml"
require_file ".env"
check_env_permissions
ensure_app_key

# ---------- Opción 3: Refresh rápido ----------
if [[ "$DO_REFRESH_NOBUILD" == "yes" ]]; then
  log "[refresh-nobuild] Reiniciando contenedores (sin build)..."
  $COMPOSE up -d "$APP_SERVICE" web "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"

  log "[refresh-nobuild] Esperando a que la app esté healthy..."
  wait_healthy "$APP_SERVICE" 120 || true

  ensure_storage_dirs
  ensure_zip_extension

  log "[refresh-nobuild] Limpiando caches de Laravel y OPcache..."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc '
    php artisan optimize:clear || true;
    php artisan view:clear || true;
    php -r "opcache_reset();" || true;
    php artisan optimize
  '
  ok "Refresh rápido completado (sin rebuild)."
  exit 0
fi

# ---------- Opción 2: Refresh completo ----------
if [[ "$DO_REFRESH" == "yes" ]]; then
  log "[refresh] Reconstruyendo imagen (BuildKit habilitado)..."
  DOCKER_BUILDKIT=1 $COMPOSE build --no-cache app

  log "[refresh] Repoblando volumen 'app_code'..."
  $COMPOSE run --rm code_init

  log "[refresh] Levantando contenedores..."
  $COMPOSE up -d "$APP_SERVICE" web "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
  wait_healthy "$APP_SERVICE" 120 || true

  ensure_storage_dirs
  ensure_zip_extension

  log "[refresh] Limpiando caches..."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc 'php artisan optimize:clear || true; php artisan view:clear || true; php -r "opcache_reset();" || true'
  ok "Refresh completo terminado."
  exit 0
fi

# ---------- Flujo normal ----------
log "Construyendo imagen de la app..."
DOCKER_BUILDKIT=1 $COMPOSE build app
ok "Imagen construida."

log "Poblando volumen 'app_code'..."
$COMPOSE run --rm code_init
ok "Volumen poblado."

log "Levantando servicios..."
$COMPOSE up -d "$DB_SERVICE" "$APP_SERVICE" web caddy "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
$COMPOSE ps

wait_healthy "$DB_SERVICE" 120
wait_healthy "$APP_SERVICE" 120
ensure_storage_dirs
ensure_zip_extension

log "Ejecutando migraciones..."
$COMPOSE exec "$APP_SERVICE" php artisan migrate --force
ok "Migraciones OK."

log "Limpieza final y optimización..."
$COMPOSE exec "$APP_SERVICE" php artisan optimize
ok "Optimización OK."

echo
ok "Deploy completado. Revisa https://${DOMAIN}"
