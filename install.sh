#!/usr/bin/env bash
set -Eeuo pipefail

# ---------- Config ----------
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.yml}"
APP_SERVICE="${APP_SERVICE:-birentable}"
DB_SERVICE="${DB_SERVICE:-pgsql}"
NODE_SERVICE="${NODE_SERVICE:-node}"
NODE_IMAGE="${NODE_IMAGE:-node:20}"
NPM_MODE="${NPM_MODE:-dev}"
SKIP_NODE="${SKIP_NODE:-no}"
NODE_RUNNER="docker-run"
SCHEDULER_SERVICE="${SCHEDULER_SERVICE:-scheduler}"
QUEUE_SERVICE="${QUEUE_SERVICE:-queue_worker}"

# ---------- Helpers ----------
log()  { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m[OK] %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m[WARN] %s\033[0m\n' "$*"; }
err()  { printf '\033[1;31m[ERR] %s\033[0m\n' "$*"; }

usage() {
  cat <<'EOF'
Uso: install.sh [--fresh] [--seed] [--refresh] [--refresh-nobuild] [--sync] [--dev] [--build]
  --sync            Sincroniza contenedores (sin npm) y corre migraciones
  (sin argumentos)  Modo interactivo
  --fresh           php artisan migrate:fresh --seed --force (BORRA datos)
  --seed            php artisan db:seed --force tras migracion normal
  --refresh         Rebuild del servicio app y recreacion de contenedores
  --refresh-nobuild Recrea contenedores sin rebuild
  --dev             Fuerza npm run dev (modo predeterminado)
  --build           Ejecuta npm run build (override del modo dev por defecto)
EOF
}

DO_FRESH="no"
DO_SEED="no"
DO_REFRESH="no"
DO_REFRESH_NOBUILD="no"
DO_SYNC_ONLY="no"

for arg in "$@"; do
  case "$arg" in
    --fresh) DO_FRESH="yes" ;;
    --seed) DO_SEED="yes" ;;
    --refresh) DO_REFRESH="yes" ;;
    --refresh-nobuild) DO_REFRESH_NOBUILD="yes" ;;
    --sync) DO_SYNC_ONLY="yes" ;;
    --dev) NPM_MODE="dev" ;;
    --build) NPM_MODE="build" ;;
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

if [[ "$DO_REFRESH" == "yes" && "$DO_REFRESH_NOBUILD" == "yes" ]]; then
  err "--refresh y --refresh-nobuild no pueden usarse juntos"
  exit 2
fi

if [[ $# -eq 0 ]]; then
  if [[ -t 0 ]]; then
    echo "Selecciona una accion para entorno de desarrollo:" >&2
    echo "  1) Instalacion completa (npm dev + migrar) [default]" >&2
    echo "  2) Refresh (build contenedor + reinstalar deps)" >&2
    echo "  3) Refresh rapido (recrear contenedores sin build)" >&2
    echo "  4) Fresh (migrate:fresh --seed --force) [BORRA DATOS]" >&2
    echo "  5) Solo seed (db:seed --force)" >&2
    echo "  6) Sync rapido (sin npm, solo migraciones y caches)" >&2
    echo "  0) Salir" >&2
    read -r -p "Opcion [1/2/3/4/5/6/0]: " opt
    case "$opt" in
      1|"") ;;
      2) DO_REFRESH="yes" ;;
      3) DO_REFRESH_NOBUILD="yes" ;;
      4) DO_FRESH="yes" ;;
      5) DO_SEED="yes" ;;
      6) DO_SYNC_ONLY="yes" ;;
      0) exit 0 ;;
      *) echo "Opcion no valida" >&2; exit 2 ;;
    esac
  else
    usage
    exit 2
  fi
fi

RUN_NODE_STEPS="yes"
if [[ "$SKIP_NODE" == "yes" ]]; then
  RUN_NODE_STEPS="no"
fi
if [[ "$DO_SYNC_ONLY" == "yes" ]]; then
  RUN_NODE_STEPS="no"
fi

if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  COMPOSE_BASE=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE_BASE=(docker-compose)
else
  err "No se encontro docker compose ni docker-compose en el sistema."
  exit 1
fi
COMPOSE_CMD=("${COMPOSE_BASE[@]}" -f "$COMPOSE_FILE")
COMPOSE_HUMAN="${COMPOSE_CMD[*]}"

compose() {
  "${COMPOSE_CMD[@]}" "$@"
}

require_file() {
  [[ -f "$1" ]] || { err "Falta $1"; exit 1; }
}

ensure_env_file() {
  if [[ ! -f .env ]]; then
    require_file ".env.example"
    log "No existe .env en el host. Copiando .env.example..."
    cp .env.example .env
    ok ".env creado."
  fi
}

check_env_permissions() {
  local perm=""
  if command -v stat >/dev/null 2>&1; then
    perm=$(stat -c "%a" .env 2>/dev/null || stat -f "%Lp" .env 2>/dev/null || echo "")
  fi
  if [[ -n "$perm" && "$perm" != "600" && "$perm" != "640" && "$perm" != "660" ]]; then
    warn ".env tiene permisos $perm (recomendado 600/640/660)."
  fi
}

ensure_app_key() {
  log "Verificando APP_KEY en .env (host)."
  if grep -qE '^APP_KEY=' .env; then
    local cur
    cur=$(grep -E '^APP_KEY=' .env | sed 's/^APP_KEY=//')
    if [[ -n "$cur" && "$cur" != "base64:" ]]; then
      ok "APP_KEY ya presente."
      return 0
    fi
    warn "APP_KEY vacio. Se generara uno nuevo."
  else
    warn "No existe linea APP_KEY en .env. Se agregara."
  fi

  local key
  if command -v openssl >/dev/null 2>&1; then
    key=$(openssl rand -base64 32 | tr -d '\n')
  else
    key=$(head -c 32 /dev/urandom | base64 | tr -d '\n')
  fi

  if grep -qE '^APP_KEY=' .env; then
    if sed --version >/dev/null 2>&1; then
      sed -i -E "s#^APP_KEY=.*#APP_KEY=base64:${key}#" .env
    else
      sed -i '' -E "s#^APP_KEY=.*#APP_KEY=base64:${key}#" .env
    fi
  else
    printf "\nAPP_KEY=base64:%s\n" "$key" >> .env
  fi
  ok "APP_KEY generado."
}

wait_service() {
  local svc="$1" timeout="${2:-120}"
  log "Esperando a que '$svc' este listo (timeout ${timeout}s)."
  local start now
  start=$(date +%s)
  while true; do
    local info
    info=$(compose ps "$svc" 2>/dev/null | tail -n +2 || true)
    if [[ -n "$info" ]]; then
      if echo "$info" | grep -qi 'unhealthy'; then
        compose ps "$svc" || true
        err "Servicio '$svc' informado como unhealthy."
        exit 1
      fi
      if echo "$info" | grep -qi 'health: starting'; then
        :
      elif echo "$info" | grep -qi 'healthy'; then
        ok "Servicio '$svc' en ejecucion (healthy)."
        return 0
      elif echo "$info" | grep -qi 'Up'; then
        if ! echo "$info" | grep -q '('; then
          ok "Servicio '$svc' en ejecucion."
          return 0
        fi
      fi
    fi
    now=$(date +%s)
    if (( now - start > timeout )); then
      compose ps "$svc" || true
      err "Timeout esperando a '$svc'."
      exit 1
    fi
    sleep 2
  done
}

exec_app() {
  compose exec -T "$APP_SERVICE" "$@"
}

exec_app_root() {
  compose exec --user root -T "$APP_SERVICE" "$@"
}

ensure_zip_extension() {
  log "Verificando ZipArchive dentro de $APP_SERVICE..."
  if exec_app php -r "exit(extension_loaded('zip') ? 0 : 1);"; then
    ok "ZipArchive disponible."
    return
  fi

  warn "ZipArchive no esta disponible. Intentando instalar dentro del contenedor..."
  exec_app_root sh -lc 'set -e
if command -v apt-get >/dev/null 2>&1; then
  apt-get update
  (apt-get install -y php-zip || apt-get install -y php8.4-zip)
  apt-get clean
elif command -v apk >/dev/null 2>&1; then
  apk add --no-cache php82-zip || apk add --no-cache php8-zip || apk add --no-cache php-zip
else
  echo "No se pudo determinar gestor de paquetes para ZipArchive" >&2
  exit 1
fi'

  if exec_app php -r "exit(extension_loaded('zip') ? 0 : 1);"; then
    ok "ZipArchive instalada."
  else
    err "ZipArchive sigue ausente despues del intento de instalacion."
    exit 1
  fi
}

composer_install() {
  log "Instalando dependencias PHP (composer install)..."
  exec_app composer install --no-interaction --prefer-dist
  ok "Composer install completado."
}

run_migrations() {
  if [[ "$DO_FRESH" == "yes" ]]; then
    warn "Ejecutando migrate:fresh --seed --force (borra datos)."
    exec_app php artisan migrate:fresh --seed --force
    ok "migrate:fresh completado."
  else
    log "Ejecutando migraciones pendientes..."
    exec_app php artisan migrate --force
    ok "Migraciones completadas."
    if [[ "$DO_SEED" == "yes" ]]; then
      log "Ejecutando seeders..."
      exec_app php artisan db:seed --force
      ok "Seeders completados."
    fi
  fi
}

storage_link() {
  log "Creando storage:link si es necesario..."
  exec_app php artisan storage:link >/dev/null 2>&1 || true
  ok "storage:link verificado."
}

sync_lang_assets() {
  if [[ -x scripts/sync-lang-assets.sh ]]; then
    log "Sincronizando assets de idiomas..."
    bash scripts/sync-lang-assets.sh || true
    ok "Assets de idiomas sincronizados."
  else
    log "scripts/sync-lang-assets.sh no encontrado; omito sincronizacion."
  fi
}

detect_node_runner() {
  if compose config --services 2>/dev/null | grep -qx "$NODE_SERVICE"; then
    NODE_RUNNER="compose"
  else
    NODE_RUNNER="docker-run"
  fi
}

run_node() {
  if [[ "$NODE_RUNNER" == "compose" ]]; then
    compose run --rm -T "$NODE_SERVICE" "$@"
  else
    docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app "$NODE_IMAGE" "$@"
  fi
}

handle_node_assets() {
  if [[ "$RUN_NODE_STEPS" != "yes" ]]; then
    log "Pasos de Node omitidos (RUN_NODE_STEPS=no)."
    return
  fi
  if [[ ! -f package.json ]]; then
    log "No existe package.json; omito pasos de Node."
    return
  fi

  detect_node_runner

  log "Instalando dependencias Node (npm ci)..."
  run_node npm ci
  ok "Dependencias Node instaladas."

  if [[ "$NPM_MODE" == "dev" ]]; then
    log "Ejecutando npm run dev (una vez para verificar)."
    if [[ "$NODE_RUNNER" == "compose" ]]; then
      warn "Para dejar Vite activo ejecuta: ${COMPOSE_HUMAN} run --rm -T ${NODE_SERVICE} npm run dev"
    else
      warn "Para dejar Vite activo ejecuta: docker run --rm -u \"$(id -u):$(id -g)\" -v \"$PWD:/app\" -w /app ${NODE_IMAGE} npm run dev"
    fi
    run_node npm run dev -- --debug --force --host || true
    ok "npm run dev ejecutado (revisa salida)."
  else
    log "Compilando assets (npm run build)..."
    run_node npm run build
    ok "Build de assets completado."
  fi
}

clear_caches() {
  log "Limpiando caches de Laravel..."
  exec_app php artisan optimize:clear || true
  exec_app php artisan view:clear || true
  exec_app php artisan route:clear || true
  exec_app php artisan config:clear || true
  exec_app php artisan optimize
  ok "Caches limpiadas y optimizadas."
}

manifest_check() {
  if exec_app test -f public/build/manifest.json; then
    ok "public/build/manifest.json presente."
  elif exec_app test -f public/hot; then
    ok "Detectado public/hot (modo dev de Vite)."
  else
    if [[ "$RUN_NODE_STEPS" == "yes" ]]; then
      err "No se encontro public/build/manifest.json ni public/hot. Revisa la compilacion de assets."
      exit 1
    else
      warn "No se encontro public/build/manifest.json ni public/hot (pasos de Node omitidos)."
    fi
  fi
}

bring_up_services() {
  local rebuild="$1" recreate="$2"
  if [[ "$rebuild" == "yes" ]]; then
    log "Construyendo servicio ${APP_SERVICE}..."
    compose build "$APP_SERVICE"
    ok "Build de ${APP_SERVICE} completado."
  fi

  if [[ "$recreate" == "yes" ]]; then
    log "Levantando servicios con recreacion (${DB_SERVICE}, ${APP_SERVICE}, ${SCHEDULER_SERVICE}, ${QUEUE_SERVICE})..."
    compose up -d --force-recreate "$DB_SERVICE" "$APP_SERVICE" "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
  else
    log "Levantando servicios (${DB_SERVICE}, ${APP_SERVICE}, ${SCHEDULER_SERVICE}, ${QUEUE_SERVICE})..."
    compose up -d "$DB_SERVICE" "$APP_SERVICE" "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
  fi
}

sync_flow() {
  log "[sync] Levantando servicios sin rebuild."
  compose up -d "$DB_SERVICE" "$APP_SERVICE" "$SCHEDULER_SERVICE" "$QUEUE_SERVICE"
  compose ps "$APP_SERVICE" "$SCHEDULER_SERVICE" "$QUEUE_SERVICE" || true
  wait_service "$DB_SERVICE" 120 || true
  wait_service "$APP_SERVICE" 120 || true
  ensure_zip_extension

  log "[sync] Actualizando dependencias PHP."
  exec_app composer install --no-interaction --prefer-dist
  ok "[sync] Composer listo."

  run_migrations
  storage_link
  sync_lang_assets
  clear_caches

  echo
  ok "[sync] Entorno sincronizado."
}

full_flow() {
  local rebuild="${1:-no}" recreate="${2:-no}"

  bring_up_services "$rebuild" "$recreate"
  compose ps

  wait_service "$DB_SERVICE" 120 || true
  wait_service "$APP_SERVICE" 120 || true

  ensure_zip_extension
  composer_install
  run_migrations
  storage_link
  sync_lang_assets
  handle_node_assets
  clear_caches
  manifest_check

  echo
  ok "Instalacion completa. Servicios listos."
}

command -v docker >/dev/null 2>&1 || { err "Docker no esta instalado o no esta en PATH."; exit 1; }
require_file "$COMPOSE_FILE"
ensure_env_file
ensure_app_key
check_env_permissions

if [[ "$DO_SYNC_ONLY" == "yes" ]]; then
  sync_flow
  exit 0
fi

REBUILD_FLAG="no"
RECREATE_FLAG="no"
if [[ "$DO_REFRESH" == "yes" ]]; then
  REBUILD_FLAG="yes"
  RECREATE_FLAG="yes"
fi
if [[ "$DO_REFRESH_NOBUILD" == "yes" ]]; then
  RECREATE_FLAG="yes"
fi

full_flow "$REBUILD_FLAG" "$RECREATE_FLAG"


