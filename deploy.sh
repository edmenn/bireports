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
    return 1
  fi
  ok "ZipArchive disponible."
}

usage() {
  echo "Uso: $0 [--fresh] [--seed] [--refresh] [--refresh-nobuild] [--sync] [--sync-migrate] [--cleanup]"
  echo "  --sync           = Sincroniza solo archivos (rsync) + limpia caches/opcache"
  echo "  --sync-migrate   = Sincroniza archivos + migraciones + limpia caches/opcache"
  echo "  (sin argumentos) = modo interactivo"
  echo "  --fresh           = php artisan migrate:fresh --seed --force (BORRA datos)"
  echo "  --seed            = php artisan db:seed --force"
  echo "  --refresh         = Reconstruye imagen, repuebla app_code, recrea contenedores y limpia cachés"
  echo "  --refresh-nobuild = Reinicia contenedores existentes y limpia cachés (rápido)"
  echo "  --cleanup         = Limpieza segura de Docker (sin riesgo)"
}

main() {
# ---------- Flags ----------
DO_FRESH="no"
DO_SEED="no"
DO_REFRESH="no"
DO_REFRESH_NOBUILD="no"
DO_SYNC_ONLY="no"
DO_SYNC_MIGRATE="no"
DO_CLEANUP="no"

for arg in "$@"; do
  case "$arg" in
    --fresh) DO_FRESH="yes" ;;
    --seed)  DO_SEED="yes" ;;
    --refresh) DO_REFRESH="yes" ;;
    --refresh-nobuild) DO_REFRESH_NOBUILD="yes" ;;
    --sync) DO_SYNC_ONLY="yes" ;;
    --sync-migrate) DO_SYNC_MIGRATE="yes" ;;
    --cleanup) DO_CLEANUP="yes" ;;
    -h|--help) usage; return 0 ;;
    *) usage; return 2 ;;
  esac
done

if [[ "$DO_SYNC_ONLY" == "yes" && "$DO_SYNC_MIGRATE" == "yes" ]]; then
  err "--sync y --sync-migrate no se pueden combinar"
  return 2
fi

if [[ "$DO_SYNC_ONLY" == "yes" || "$DO_SYNC_MIGRATE" == "yes" ]]; then
  if [[ "$DO_REFRESH" == "yes" || "$DO_REFRESH_NOBUILD" == "yes" || "$DO_FRESH" == "yes" ]]; then
    err "--sync/--sync-migrate no se puede combinar con --refresh/--refresh-nobuild/--fresh"
    return 2
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
  echo "  6) Sync rapido (solo archivos + limpiar caches)"
  echo "  7) Sync rapido + migraciones (solo archivos + migrar + limpiar caches)"
  echo "  8) Limpieza segura de Docker (sin riesgo)"
  echo "  0) Salir"
  read -r -p "Opción [1/2/3/4/5/6/7/8/0]: " opt
  case "$opt" in
    1|"") ;;
    2) DO_REFRESH="yes" ;;
    3) DO_REFRESH_NOBUILD="yes" ;;
    4) DO_FRESH="yes" ;;
    5) DO_SEED="yes" ;;
    6) DO_SYNC_ONLY="yes" ;;
    7) DO_SYNC_MIGRATE="yes" ;;
    8) DO_CLEANUP="yes" ;;
    0) return 0 ;;
    *) echo "Opción inválida"; return 2 ;;
  esac
fi

# ---------- Funciones auxiliares ----------
require_file() { [[ -f "$1" ]] || { err "Falta $1"; return 1; }; }

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
      return 1
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
command -v docker >/dev/null || { err "Docker no está instalado/en PATH"; return 1; }
require_file "docker-compose.secure.yml" || return 1
require_file ".env" || return 1
check_env_permissions
ensure_app_key

# ---------- Limpieza segura de Docker ----------
if [[ "$DO_CLEANUP" == "yes" ]]; then
  log "[cleanup] Verificando contenedores activos..."
  RUNNING=$($COMPOSE ps -q | wc -l)
  if [[ "$RUNNING" -eq 0 ]]; then
    warn "No hay contenedores activos. Levantando servicios antes de limpiar..."
    $COMPOSE up -d
    sleep 5
  fi

  log "[cleanup] Ejecutando limpieza segura de Docker (sin borrar volúmenes ni imágenes activas)..."
  BEFORE=$(docker system df | grep "Total space" || true)
  docker system prune -f
  log "[cleanup] Estado del sistema tras limpieza:"
  docker system df
  ok "Limpieza completada con éxito. Docker sin elementos huérfanos."
  return 0
fi

# ---------- Sync rapido ----------
if [[ "$DO_SYNC_ONLY" == "yes" ]]; then
  log "[sync] Sincronizando archivos al volumen de produccion..."
  $COMPOSE run --rm code_sync
  log "[sync] Limpiando caches y reiniciando OPcache..."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc '
    php artisan view:clear || true;
    php -r "opcache_reset();" || true;
    php artisan optimize
  '
  log "[sync] Reiniciando PHP-FPM para limpiar OPcache..."
  $COMPOSE restart "$APP_SERVICE"
  ok "Sincronizacion rapida completada."
  return 0
fi

# ---------- Sync rapido + migraciones ----------
if [[ "$DO_SYNC_MIGRATE" == "yes" ]]; then
  log "[sync-migrate] Sincronizando archivos al volumen de produccion..."
  $COMPOSE run --rm code_sync
  log "[sync-migrate] Ejecutando migraciones..."
  $COMPOSE exec -T "$APP_SERVICE" php artisan migrate --force
  log "[sync-migrate] Limpiando caches y reiniciando OPcache..."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc '
    php artisan view:clear || true;
    php -r "opcache_reset();" || true;
    php artisan optimize
  '
  log "[sync-migrate] Reiniciando PHP-FPM para limpiar OPcache..."
  $COMPOSE restart "$APP_SERVICE"
  ok "Sincronizacion rapida con migraciones completada."
  return 0
fi

# ---------- Refresh rápido ----------
if [[ "$DO_REFRESH_NOBUILD" == "yes" ]]; then
  log "[refresh-nobuild] Reiniciando contenedores (sin build)..."
  $COMPOSE up -d "$APP_SERVICE" web "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
  wait_healthy "$APP_SERVICE" 120 || true
  ensure_storage_dirs
  ensure_zip_extension || return 1
  log "[refresh-nobuild] Limpiando caches de Laravel y OPcache..."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc '
    php artisan optimize:clear || true;
    php artisan view:clear || true;
    php -r "opcache_reset();" || true;
    php artisan optimize
  '
  log "[refresh-nobuild] Reiniciando PHP-FPM para limpiar OPcache..."
  $COMPOSE restart "$APP_SERVICE"
  ok "Refresh rápido completado."
  return 0
fi

# ---------- Refresh completo ----------
if [[ "$DO_REFRESH" == "yes" ]]; then
  log "[refresh] Reconstruyendo imagen (BuildKit habilitado)..."
  DOCKER_BUILDKIT=1 $COMPOSE build --no-cache app
  log "[refresh] Repoblando volumen 'app_code'..."
  $COMPOSE run --rm code_init
  log "[refresh] Levantando contenedores..."
  $COMPOSE up -d "$APP_SERVICE" web "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
  wait_healthy "$APP_SERVICE" 120 || true
  ensure_storage_dirs
  ensure_zip_extension || return 1
  log "[refresh] Limpiando caches..."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc 'php artisan optimize:clear || true; php artisan view:clear || true; php -r "opcache_reset();" || true'
  log "[refresh] Reiniciando PHP-FPM para limpiar OPcache..."
  $COMPOSE restart "$APP_SERVICE"
  ok "Refresh completo terminado."
  return 0
fi

# ---------- Deploy normal ----------
log "Construyendo imagen de la app..."
DOCKER_BUILDKIT=1 $COMPOSE build app
ok "Imagen construida."
log "Poblando volumen 'app_code'..."
$COMPOSE run --rm code_init
ok "Volumen 'app_code' poblado."
log "Levantando servicios..."
$COMPOSE up -d "$DB_SERVICE" "$APP_SERVICE" web caddy "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
$COMPOSE ps
wait_healthy "$DB_SERVICE" 120 || return 1
wait_healthy "$APP_SERVICE" 120 || return 1
ensure_storage_dirs
ensure_zip_extension || return 1
log "Ejecutando migraciones..."
$COMPOSE exec "$APP_SERVICE" php artisan migrate --force
ok "Migraciones OK."
log "Limpieza final y optimización..."
$COMPOSE exec "$APP_SERVICE" php artisan optimize
log "Reiniciando PHP-FPM para limpiar OPcache..."
$COMPOSE restart "$APP_SERVICE"
ok "Optimización OK."
echo
ok "Deploy completado. Revisa https://${DOMAIN}"
}

main "$@"
rc=$?
if [[ "${BASH_SOURCE[0]}" != "$0" ]]; then
  return "$rc"
fi
exit "$rc"
