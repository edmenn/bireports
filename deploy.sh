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
ensure_zip_extension() {
  log "Verificando extension ZipArchive..."
  if ! $COMPOSE exec -T "$APP_SERVICE" php -r "exit(extension_loaded('zip') ? 0 : 1);"; then
    err "ZipArchive no esta habilitada en la imagen. Revisa Dockerfile.app."
    exit 1
  fi
  ok "ZipArchive disponible."
}
usage() {
  echo "Uso: $0 [--fresh] [--seed] [--refresh] [--refresh-nobuild]"
  echo "  (sin argumentos) = modo interactivo (elige acción en un menú)"
  echo "  --fresh           = php artisan migrate:fresh --seed --force (BORRA datos)"
  echo "  --seed            = php artisan db:seed --force (tras migración normal)"
  echo "  --refresh         = Refresca código reconstruyendo imagen, repuebla app_code,"
  echo "                      recrea app/web, limpia caches y opcache_reset"
  echo "  --refresh-nobuild = Solo limpiar caches/opcache y recrear contenedores"
}

DO_FRESH="no"
DO_SEED="no"
DO_REFRESH="no"
DO_REFRESH_NOBUILD="no"

for arg in "$@"; do
  case "$arg" in
    --fresh) DO_FRESH="yes" ;;
    --seed)  DO_SEED="yes"  ;;
    --refresh) DO_REFRESH="yes" ;;
    --refresh-nobuild) DO_REFRESH_NOBUILD="yes" ;;
    -h|--help) usage; exit 0 ;;
    *) usage; exit 2 ;;
  esac
done

# Si no hay argumentos, ofrecer menú interactivo (si hay TTY)
if [[ $# -eq 0 ]]; then
  if [[ -t 0 ]]; then
    echo "Selecciona una acción:" >&2
    echo "  1) Deploy normal (build + migrar)" >&2
    echo "  2) Refresh (build + repoblar + limpiar caches)" >&2
    echo "  3) Refresh rápido (sin build, limpiar caches/opcache)" >&2
    echo "  4) Fresh (migrate:fresh --seed --force) [BORRA DATOS]" >&2
    echo "  5) Solo seed (db:seed --force)" >&2
    echo "  0) Salir" >&2
    read -r -p "Opción [1/2/3/4/5/0]: " opt
    case "$opt" in
      1|"") ;; # deploy normal por defecto
      2) DO_REFRESH="yes" ;;
      3) DO_REFRESH_NOBUILD="yes" ;;
      4) DO_FRESH="yes" ;;
      5) DO_SEED="yes" ;;
      0) exit 0 ;;
      *) echo "Opción inválida" >&2; exit 2 ;;
    esac
  else
    usage; exit 2
  fi
fi

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

ensure_storage_dirs() {
  log "Asegurando storage/* y bootstrap/cache dentro del contenedor."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc '
    set -e
    mkdir -p storage/framework/{cache,views,sessions} storage/logs \
      && chmod -R 775 storage bootstrap/cache \
      && chown -R 1000:1000 storage bootstrap/cache
  '
  ok "Directorios de storage y cache listos."
}

sync_storage_lang_to_volume() {
  if [[ -d storage/app/public/lang ]]; then
    log "Sincronizando storage/app/public/lang al volumen de 'app'…"
    # Crear destino dentro del contenedor
    $COMPOSE exec -T "$APP_SERVICE" sh -lc 'mkdir -p storage/app/public/lang'
    # Obtener container ID para docker cp
    CID=$($COMPOSE ps -q "$APP_SERVICE")
    if [[ -n "$CID" ]]; then
      docker cp -a storage/app/public/lang/. "$CID":/var/www/html/storage/app/public/lang/
      ok "storage/app/public/lang sincronizado al volumen."
    else
      warn "No pude obtener el contenedor de $APP_SERVICE para copiar lang."
    fi
  else
    log "No existe storage/app/public/lang en el host; omito sincronizacin."
  fi
}

# Asegura que .env (host) tenga APP_KEY no vacío antes de levantar contenedores
ensure_app_key() {
  log "Verificando APP_KEY en .env (host)."
  if grep -qE '^APP_KEY=' .env; then
    local cur
    cur=$(grep -E '^APP_KEY=' .env | sed 's/^APP_KEY=//')
    if [[ -n "$cur" && "$cur" != "base64:" ]]; then
      ok "APP_KEY ya presente."
      return 0
    fi
    warn "APP_KEY vacío. Generando uno nuevo."
  else
    warn "No existe línea APP_KEY en .env. La crearé."
  fi

  # Generar 32 bytes aleatorios en base64
  local key
  if command -v openssl >/dev/null 2>&1; then
    key=$(openssl rand -base64 32 | tr -d '\n')
  else
    key=$(head -c 32 /dev/urandom | base64 | tr -d '\n')
  fi
  if grep -qE '^APP_KEY=' .env; then
    sed -i -E "s#^APP_KEY=.*#APP_KEY=base64:${key}#" .env
  else
    printf "\nAPP_KEY=base64:%s\n" "$key" >> .env
  fi
  ok "APP_KEY generado en .env."
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
ensure_app_key

if [[ "$DO_SYNC_ONLY" == "yes" ]]; then
  log "[sync] Copiando cambios al volumen app_code (rsync incremental)."
  $COMPOSE run --rm code_sync
  ok "[sync] Código sincronizado."

  log "[sync] Reiniciando contenedores app y web (sin rebuild)."
  $COMPOSE up -d "$APP_SERVICE" web
  wait_healthy "$APP_SERVICE" 120 || true
  ensure_storage_dirs
  ensure_zip_extension
  sync_storage_lang_to_volume

  log "[sync] Ejecutando migraciones pendientes."
  $COMPOSE exec -T "$APP_SERVICE" php artisan migrate --force
  ok "[sync] Migraciones OK."
  if [[ "$DO_SEED" == "yes" ]]; then
    log "[sync] Ejecutando seeders (opcional)."
    $COMPOSE exec -T "$APP_SERVICE" php artisan db:seed --force
    ok "[sync] Seeders OK."
  fi

  log "[sync] Limpiando caches de Laravel y OPcache."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc 'php artisan optimize:clear || true; php artisan view:clear || true; php -r "opcache_reset();" || true'
  $COMPOSE exec -T "$APP_SERVICE" php artisan optimize
  ok "[sync] Deploy incremental completado."
  exit 0
fi


# ---------- Refresh-only flow ----------
if [[ "$DO_REFRESH" == "yes" ]]; then
  log "[refresh] Construyendo imagen de la app (sin cache si se desea con DOCKER_BUILDKIT=1)."
  $COMPOSE build app

  log "[refresh] Repoblando volumen 'app_code' desde la imagen nueva."
  $COMPOSE run --rm code_init

  log "[refresh] Recreando contenedores app y web."
  $COMPOSE up -d "$APP_SERVICE" web

  # Esperar a que app responda (healthcheck php -v)
  wait_healthy "$APP_SERVICE" 120 || true

  ensure_storage_dirs
  ensure_zip_extension

  log "[refresh] Limpiando caches de Laravel y OPcache."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc 'php artisan optimize:clear || true; php artisan view:clear || true; php -r "opcache_reset();" || true'
  ok "Refresh completado."
  exit 0
fi

# ---------- Refresh-no-build flow ----------
if [[ "$DO_REFRESH_NOBUILD" == "yes" ]]; then
  log "[refresh-nobuild] Recreando contenedores app y web (sin build)."
  $COMPOSE up -d "$APP_SERVICE" web
  wait_healthy "$APP_SERVICE" 120 || true
  ensure_storage_dirs
  ensure_zip_extension
  log "[refresh-nobuild] Limpiando caches de Laravel y OPcache."
  $COMPOSE exec -T "$APP_SERVICE" sh -lc 'php artisan optimize:clear || true; php artisan view:clear || true; php -r "opcache_reset();" || true'
  ok "Refresh (sin build) completado."
  exit 0
fi

# ---------- Sincronizar logos por idioma a public/lang (si existe script) ----------
if [[ -x scripts/sync-lang-assets.sh ]]; then
  log "Sincronizando assets de idiomas a public/lang…"
  bash scripts/sync-lang-assets.sh || true
  ok "Assets de idiomas sincronizados."
else
  log "scripts/sync-lang-assets.sh no encontrado; omito sync local."
fi

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

# ---------- Asegurar carpetas de escritura ----------
ensure_storage_dirs
ensure_zip_extension

# ---------- Sincronizar assets de idioma al volumen de storage ----------
sync_storage_lang_to_volume

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

log "optimizaciones… (omito storage:link; Nginx sirve /storage por alias)"
$COMPOSE exec "$APP_SERVICE" php artisan config:clear || true
$COMPOSE exec "$APP_SERVICE" php artisan route:clear  || true
$COMPOSE exec "$APP_SERVICE" php artisan view:clear   || true
$COMPOSE exec "$APP_SERVICE" php artisan optimize
ok "Optimización OK."

echo
ok "Deploy completado. Revisa https://${DOMAIN}"





