#!/bin/sh
set -eu

cd /var/www/html

QUEUE_LIST=${QUEUE_LIST:-default,capacities}

echo "[queue-worker] Starting queue worker for queues: ${QUEUE_LIST}"

exec php artisan queue:work --queue="${QUEUE_LIST}" --sleep=5 --tries=1 --no-interaction
