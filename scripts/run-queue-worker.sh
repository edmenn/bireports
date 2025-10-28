#!/bin/sh
set -eu

cd /var/www/html

echo "[queue-worker] Starting queue worker for capacities..."

exec php artisan queue:work --queue=capacities --sleep=5 --tries=1 --no-interaction
