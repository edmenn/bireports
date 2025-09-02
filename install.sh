#!/usr/bin/env bash
set -euo pipefail

# ===============================
# init.sh — Inicializador de proyecto Laravel con Docker
# - Composer install
# - APP_KEY
# - Migraciones (con opciones)
# - storage:link
# - NPM ci + build (Vite)
#
# Uso:
#   bash init.sh                # flujo estándar (migrate normal)
#   bash init.sh --seed         # migrate + db:seed
#   bash init.sh --fresh        # migrate:fresh --seed (ATENCIÓN: borra datos)
#   bash init.sh --dev          # usa `npm run dev` (hot) en lugar de `build`
# ===============================

# --------- Config local (ajusta si tu servicio tiene otro nombre)
LARAVEL_SVC="${LARAVEL_SVC:-laravel.test}"
NODE_SVC="${NODE_SVC:-node}"

# --------- Detectar comando docker compose
if command -v docker &>/dev/null && docker compose version &>/dev/null; then
  DC="docker compose"
elif command -v docker-compose &>/dev/null; then
  DC="docker-compose"
else
  echo "ERROR: No se encontró 'docker compose' ni 'docker-compose' en el sistema." >&2
  exit 1
fi

# --------- Parámetros
DO_SEED="no"
DO_FRESH="no"
NPM_MODE="build"  # build | dev

for arg in "$@"; do
  case "$arg" in
    --seed) DO_SEED="yes" ;;
    --fresh) DO_FRESH="yes" ;;
    --dev) NPM_MODE="dev" ;;
    *)
      echo "Opción desconocida: $arg" >&2
      echo "Uso: bash init.sh [--seed] [--fresh] [--dev]" >&2
      exit 2
      ;;
  esac
done

log() { printf "\033[1;34m==> %s\033[0m\n" "$*"; }
ok()  { printf "\033[1;32m✓ %s\033[0m\n" "$*"; }
warn(){ printf "\033[1;33m! %s\033[0m\n" "$*"; }
err() { printf "\033[1;31m✗ %s\033[0m\n" "$*"; }

# --------- Verificaciones básicas
log "Verificando que el servicio '$LARAVEL_SVC' esté corriendo..."
$DC ps --services | grep -qx "$LARAVEL_SVC" || { err "No existe el servicio '$LARAVEL_SVC' en docker compose."; exit 1; }

log "Verificando que el servicio '$NODE_SVC' exista (para NPM/Vite)..."
$DC ps --services | grep -qx "$NODE_SVC" || { err "No existe el servicio '$NODE_SVC' en docker compose. Define un servicio Node para ejecutar NPM."; exit 1; }

# --------- Composer install
log "Instalando dependencias PHP (composer install)..."
$DC exec "$LARAVEL_SVC" composer install --no-interaction --prefer-dist
ok "Composer install ok."

# --------- Preparar .env y APP_KEY
if ! $DC exec "$LARAVEL_SVC" test -f .env; then
  log "No existe .env. Copiando desde .env.example..."
  $DC exec "$LARAVEL_SVC" cp .env.example .env
  ok ".env creado."
fi

APP_KEY=$($DC exec -T "$LARAVEL_SVC" sh -lc 'php -r "require __DIR__.\"/vendor/autoload.php\"; \$env = file_get_contents(\".env\"); preg_match(\"/^APP_KEY=(.*)$/m\", \$env, \$m); echo isset(\$m[1]) ? trim(\$m[1]) : \"\";"')
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
  log "Generando APP_KEY..."
  $DC exec "$LARAVEL_SVC" php artisan key:generate --force
  ok "APP_KEY generado."
else
  ok "APP_KEY ya presente."
fi

# --------- Migraciones / Seed
if [ "$DO_FRESH" = "yes" ]; then
  warn "Ejecutando migrate:fresh --seed (esto borrará datos)."
  $DC exec "$LARAVEL_SVC" php artisan migrate:fresh --seed --force
  ok "migrate:fresh --seed ok."
else
  log "Ejecutando migraciones..."
  $DC exec "$LARAVEL_SVC" php artisan migrate --force
  ok "Migraciones ok."
  if [ "$DO_SEED" = "yes" ]; then
    log "Ejecutando seeders..."
    $DC exec "$LARAVEL_SVC" php artisan db:seed --force
    ok "Seeders ok."
  fi
fi

# --------- storage:link
log "Creando storage:link (si no existe)..."
$DC exec "$LARAVEL_SVC" php artisan storage:link || true
ok "storage:link listo."

# --------- NPM / Vite
log "Instalando dependencias Node (npm ci)..."
$DC run --rm "$NODE_SVC" npm ci
ok "Dependencias Node instaladas."

if [ "$NPM_MODE" = "dev" ]; then
  log "Levantando Vite en modo desarrollo (crea public/hot)."
  warn "Este proceso suele quedarse 'vivo'. Ejecuta este comando en otra terminal si lo necesitas en segundo plano:"
  echo "$DC run --rm $NODE_SVC npm run dev"
  # Ejecutamos una sola vez para validar; no lo dejamos colgado
  $DC run --rm "$NODE_SVC" npm run dev -- --debug --force --host || true
  ok "Vite dev lanzado (verifica logs)."
else
  log "Compilando assets de producción (npm run build)..."
  $DC run --rm "$NODE_SVC" npm run build
  ok "Build Vite ok."
fi

# --------- Limpieza de cachés de Laravel (por si acaso)
log "Limpiando cachés de Laravel..."
$DC exec "$LARAVEL_SVC" php artisan view:clear || true
$DC exec "$LARAVEL_SVC" php artisan route:clear || true
$DC exec "$LARAVEL_SVC" php artisan config:clear || true
ok "Cachés limpiadas."

# --------- Verificación final del manifest
if $DC exec "$LARAVEL_SVC" test -f public/build/manifest.json; then
  ok "manifest.json presente en public/build."
else
  if $DC exec "$LARAVEL_SVC" test -f public/hot; then
    ok "Modo dev activo (public/hot). manifest.json no es necesario."
  else
    err "No se encontró public/build/manifest.json ni public/hot. Revisa vite.config.js y el servicio Node."
    exit 1
  fi
fi

echo
ok "Init completo. Proyecto listo."
