#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

cp -n .env.example .env 2>/dev/null || true

composer install --no-interaction
php artisan key:generate --force

# Branding PNGs (icon + splash) — lesson 3.8
php generate-branding-assets.php

if [[ -f package.json ]]; then
  npm install --ignore-scripts 2>/dev/null || npm install
  npm run build
fi

echo ""
echo "Field Notes (Chapter 4) ready."
echo "  Browser:  php artisan serve"
echo "  Native:   php artisan native:install --force && php artisan native:run ios"
