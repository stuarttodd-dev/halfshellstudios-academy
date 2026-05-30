#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

cp -n .env.example .env 2>/dev/null || true

composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force

echo ""
echo "Field Notes ready."
echo "  Browser:  php artisan serve --host=127.0.0.1 --port=8016"
echo "  Tests:    php artisan test"
echo "  Native:   php artisan native:install && php artisan native:run  (after chapter 3 setup)"
