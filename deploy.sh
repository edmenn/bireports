#!/usr/bin/env bash
set -Eeuo pipefail

COMPOSE="docker compose -f docker-compose.secure.yml"
APP_SERVICE="app"   # nombre del servicio (no container_name)
DOMAIN="${DOMAIN:-portal.birentable.com}"

log()  { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✓ %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m! %s\033[0m\n' "$*"; }
err()  { printf '\033[1;31m✗ %s\033[0m\n' "$*"; }

# 0) Requisitos de seguridad: .env debe existir y tener permisos restrictivos
if [[ ! -f .env ]]; then
  err "Falta .env en la raíz del proyecto. No se crea automáticamente por seguridad."
  echo "Crea uno a partir de .env.example (sin subirlo al repo) y vuelve a ejecutar."
  exit 1
fi

# Advertir si permisos son muy abiertos
PERM=$(stat -c "%a" .env 2>/dev/null || echo "???")
if [[ "$PERM" != "600" && "$PERM" != "640" && "$PERM" != "660" ]]; then
  warn "Permisos de .env = $PERM. Recomendado: 600/640/660."
  warn "Puedes ajustarlos con: chmod 640 .env"
fi

# 1) (Opcional) Build de frontend si existe package.json
if [[ -f package.json ]]; then
  log "Instalando dependencias Node y construyendo assets…"
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm ci
  docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:20 npm run build
  ok "Assets listos."
else
  log "Sin package.json, omito build de frontend."
fi

# 2) Build imagen de la app (Composer corre dentro del Dockerfile)
log "Construyendo imagen de la app…"
$COMPOSE build app

# 3) Poblar volumen de código desde la imagen (one-shot)
log "Poblando volumen 'app_code'…"
$COMPOSE run --rm code_init

# 4) Levantar servicios base
log "Levantando stack…"
$COMPOSE up -d pgsql app web caddy
$COMPOSE ps
ok "Servicios arriba."

# 5) Migraciones y tareas de Laravel
log "Ejecutando migraciones (modo seguro, sin exponer secretos)…"
$COMPOSE exec "$APP_SERVICE" php artisan session:table   || true
$COMPOSE exec "$APP_SERVICE" php artisan queue:table     || true
$COMPOSE exec "$APP_SERVICE" php artisan cache:table     || true
$COMPOSE exec "$APP_SERVICE" php artisan migrate --force

log "storage:link + optimize…"
$COMPOSE exec "$APP_SERVICE" php artisan storage:link || true
$COMPOSE exec "$APP_SERVICE" php artisan config:clear || true
$COMPOSE exec "$APP_SERVICE" php artisan route:clear  || true
$COMPOSE exec "$APP_SERVICE" php artisan view:clear   || true
$COMPOSE exec "$APP_SERVICE" php artisan optimize

ok "Deploy completado. Revisa https://${DOMAIN}"
