#!/bin/sh
set -e

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

# Supabase ya tiene el esquema SQL; no forzar migraciones en producción
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction || true
fi

php artisan optimize:clear
php artisan config:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
