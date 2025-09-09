#!/usr/bin/env bash
set -euo pipefail

# ===============================
# init.sh — Inicializador de proyecto Laravel con Docker (producción)
# - Composer install (en contenedor efímero)
# - Detecta runtime de Sail y ajusta docker-compose.yml
# - docker compose up con override de prod (Caddy)
# - APP_KEY, migraciones (normal/fresh/seed), storage:link
# - Build de assets con Node (en contenedor efímero)
#
# Uso:
#   bash init.sh                # flujo estándar (migrate normal)
#   bash init.sh --seed         # migrate + db:seed
#   bash init.sh --fresh        # migrate:fresh --seed  (ATENCIÓN borra datos)
# ===============================

# -------- Configura tu servicio app (TU NOMBRE REAL) --------
LARAVEL_SVC="${LARAVEL_SVC:-birentable}"   # <- tu servicio se llama 'birentable'
COMPOSE_BASE="${COMPOSE_BASE:-docker-compose.yml}"
COMPOSE_PROD="${COMPOSE_PROD:-docker-compose.prod.yml}"

# -------- Detectar docker compose --------
if command -v docker &>/dev/null && docker compose version &>/dev/null; then
  DC="docker compose"
elif command -v docker-compose &>/dev/null; then
  DC="docker-compose"
else
  echo "ERROR: No se encontró 'docker compose' ni 'docker-compose' en el sistema." >&2
  exit 1
fi

log() { printf "\033[1;34m==> %s\033[0m\n" "$*"; }
ok()  { printf "\033[1;32m✓ %s\033[0m\n" "$*"; }
warn(){ printf "\033[1;33m! %s\033[0m\n" "$*"; }
err() { printf "\033[1;31m✗ %s\033[0m\n" "$*"; }

# -------- Parámetros --------
DO_SEED="no"
DO_FRESH="no"

for arg in "${@:-}"; do
  case "$arg" in
    --seed) DO_SEED="yes" ;;
    --fresh) DO_FRESH="yes" ;;
    *)
      echo "Uso: bash init.sh [--seed] [--fresh]" >&2
      exit 2
      ;;
  esac
done

# -------- 1) Composer install (genera vendor/ sin contenedores previos) --------
log "Instalando dependencias PHP con Composer (contenedor efímero)..."
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$PWD:/app" -w /app \
  composer:2 install --no-dev --optimize-autoloader --no-interaction
ok "Composer install listo (vendor/ creado)."

# -------- 2) Detectar runtime de Sail y ajustar docker-compose.yml si hace falta --------
RUNTIME_BASE="vendor/laravel/sail/runtimes"
if [[ ! -d "$RUNTIME_BASE" ]]; then
  err "No existe $RUNTIME_BASE. ¿laravel/sail está en composer.json?"
  exit 1
fi

RUNTIME_VERSION="$(ls -1 "$RUNTIME_BASE" | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -1 || true)"
if [[ -z "${RUNTIME_VERSION:-}" ]]; then
  err "No se detectó carpeta de runtime (8.x) en $RUNTIME_BASE"
  exit 1
fi
RUNTIME_PATH="./$RUNTIME_BASE/$RUNTIME_VERSION"
if [[ ! -f "$RUNTIME_BASE/$RUNTIME_VERSION/Dockerfile" ]]; then
  err "Falta Dockerfile en $RUNTIME_BASE/$RUNTIME_VERSION"
  exit 1
fi

log "Runtime de Sail detectado: $RUNTIME_VERSION"
log "Verificando/ajustando context en $COMPOSE_BASE para servicio '$LARAVEL_SVC'..."
cp "$COMPOSE_BASE" "$COMPOSE_BASE.bak.$(date +%Y%m%d-%H%M%S)"

# Reemplazo de context de runtimes/X.Y (tanto comillas simples como dobles)
sed -E -i "s|(^[[:space:]]*context:[[:space:]]*')\./vendor/laravel/sail/runtimes/[0-9]+\.[0-9]+'|\1$RUNTIME_PATH'|g" "$COMPOSE_BASE"
sed -E -i "s|(^[[:space:]]*context:[[:space:]]*\")\./vendor/laravel/sail/runtimes/[0-9]+\.[0-9]+\"|\1$RUNTIME_PATH\"|g" "$COMPOSE_BASE"

# Verificación de nombre de servicio
if ! grep -qE "^[[:space:]]*$LARAVEL_SVC:[[:space:]]*$" "$COMPOSE_BASE"; then
  err "No se encontró el servicio '$LARAVEL_SVC:' en $COMPOSE_BASE. Ajusta LARAVEL_SVC o el nombre del servicio."
  exit 1
fi
ok "docker-compose.yml alineado con runtime $RUNTIME_VERSION."

# -------- 3) Levantar stack (con override de producción si existe) --------
if [[ -f "$COMPOSE_PROD" ]]; then
  log "Levantando con override de producción: $COMPOSE_BASE + $COMPOSE_PROD ..."
  $DC -f "$COMPOSE_BASE" -f "$COMPOSE_PROD" up -d --build
else
  warn "No se encontró $COMPOSE_PROD; levantando solo $COMPOSE_BASE ..."
  $DC -f "$COMPOSE_BASE" up -d --build
fi
ok "Contenedores arriba."
$DC ps

# -------- 4) .env, APP_KEY, migraciones, storage:link --------
log "Asegurando .env dentro del contenedor ($LARAVEL_SVC)..."
if ! $DC exec "$LARAVEL_SVC" test -f .env; then
  $DC exec "$LARAVEL_SVC" cp .env.example .env
  ok ".env creado desde .env.example"
fi

log "Generando APP_KEY (si falta)..."
$DC exec "$LARAVEL_SVC" php artisan key:generate --force || true
ok "APP_KEY listo."

if [[ "$DO_FRESH" == "yes" ]]; then
  warn "Ejecutando migrate:fresh --seed (ELIMINA DATOS)."
  $DC exec "$LARAVEL_SVC" php artisan migrate:fresh --seed --force
  ok "migrate:fresh --seed ok."
else
  log "Ejecutando migraciones..."
  $DC exec "$LARAVEL_SVC" php artisan migrate --force
  ok "Migraciones ok."
  if [[ "$DO_SEED" == "yes" ]]; then
    log "Ejecutando seeders..."
    $DC exec "$LARAVEL_SVC" php artisan db:seed --force
    ok "Seeders ok."
  fi
fi

log "Creando storage:link (idempotente)..."
$DC exec "$LARAVEL_SVC" php artisan storage:link || true
ok "storage:link listo."

# -------- 5) Build de assets (Node) en contenedor efímero --------
if [[ -f package.json ]]; then
  log "Instalando dependencias Node (npm ci) en contenedor efímero..."
  docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$PWD:/app" -w /app \
    node:20 npm ci

  log "Compilando assets de producción (npm run build)..."
  docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$PWD:/app" -w /app \
    node:20 npm run build
  ok "Build de Vite ok."
else
  warn "No se encontró package.json. Omitiendo build de frontend."
fi

# -------- 6) Limpiar cachés y verificación de manifest --------
log "Limpiando cachés de Laravel..."
$DC exec "$LARAVEL_SVC" php artisan view:clear || true
$DC exec "$LARAVEL_SVC" php artisan route:clear || true
$DC exec "$LARAVEL_SVC" php artisan config:clear || true
$DC exec "$LARAVEL_SVC" php artisan optimize
ok "Cachés listas."

if $DC exec "$LARAVEL_SVC" test -f public/build/manifest.json; then
  ok "manifest.json presente en public/build (modo producción)."
else
  if $DC exec "$LARAVEL_SVC" test -f public/hot; then
    ok "Se detectó public/hot (modo dev de Vite)."
  else
    warn "No se encontró public/build/manifest.json ni public/hot. Revisa vite.config.js si no ves estilos/JS."
  fi
fi

echo
ok "Init completo. Visita tu dominio (p.ej. https://portal.birentable.com)."
