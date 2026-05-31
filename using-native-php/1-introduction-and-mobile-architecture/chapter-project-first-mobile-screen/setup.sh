#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

cp -n .env.example .env 2>/dev/null || true

composer install --no-interaction
php artisan key:generate --force

echo ""
echo "Laravel ready. Next steps (see README.md):"
echo "  1. php artisan serve          # browser check at http://127.0.0.1:8000"
echo "  2. php artisan native:install # after NATIVEPHP_APP_ID is set in .env"
echo "  3. php artisan native:jump    # scan QR with Jump on your phone"
