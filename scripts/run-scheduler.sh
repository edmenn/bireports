#!/bin/sh
set -eu

# Ensure we run from the Laravel project root
cd /var/www/html

echo "[scheduler] Starting Laravel scheduler loop..."

trap 'echo "[scheduler] Received stop signal, exiting."; exit 0' INT TERM

while true; do
    if ! php artisan schedule:run --verbose --no-interaction; then
        echo "[scheduler] schedule:run failed (exit $?)" >&2
    fi
    sleep 60
done
