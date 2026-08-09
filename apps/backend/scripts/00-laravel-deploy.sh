#!/usr/bin/env bash
set -Eeuo pipefail
cd /var/www/html

echo "Installing production dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force --no-interaction
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true

echo "Laravel deploy preparation completed"
