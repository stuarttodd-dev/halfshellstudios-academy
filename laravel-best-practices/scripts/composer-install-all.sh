#!/usr/bin/env bash
# Run from laravel-best-practices/:  bash scripts/composer-install-all.sh
set -euo pipefail
base="$(cd "$(dirname "$0")/.." && pwd)"
for d in "$base"/ch*-exercise-*/laravel; do
  if [[ -f "$d/composer.json" ]]; then
    echo "==> $(basename "$(dirname "$d")")"
    (cd "$d" && composer install --no-interaction)
  fi
done
echo "Done. Copy .env.example to .env and php artisan key:generate in each app you use."
