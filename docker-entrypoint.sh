#!/bin/sh
set -e

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Creating storage symlink..."
php artisan storage:link --quiet 2>/dev/null || true

echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting queue worker..."
# --max-time makes the worker exit hourly, so it has to be restarted
(while true; do php artisan queue:work --sleep=3 --tries=3 --max-time=3600 || sleep 5; done) &

echo "==> Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile
