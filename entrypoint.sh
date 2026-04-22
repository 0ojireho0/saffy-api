#!/bin/sh
set -e

mkdir -p /var/www/html/storage/app/public/Stories

rm -f /var/www/html/public/storage || true
php artisan storage:link || true

php artisan config:clear || true
php artisan cache:clear || true
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
