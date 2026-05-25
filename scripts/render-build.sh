#!/usr/bin/env bash
set -euo pipefail

echo "==> Composer"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Frontend (Vite)"
npm ci
npm run build

echo "==> Laravel"
php artisan storage:link 2>/dev/null || true
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Build listo"
