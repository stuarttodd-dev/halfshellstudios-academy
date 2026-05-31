#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
PROJECT="${NATIVEPHP_PROJECT:-$ROOT/../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen}"
OS="${1:-ios}"

if [[ ! -f "$PROJECT/artisan" ]]; then
  echo "Laravel project not found: $PROJECT" >&2
  echo "Set NATIVEPHP_PROJECT to your Chapter 1 app path." >&2
  exit 1
fi

cd "$PROJECT"

if [[ ! -f .env ]]; then
  cp -n .env.example .env
  php artisan key:generate --force
fi

if ! grep -q '^NATIVEPHP_APP_ID=' .env 2>/dev/null; then
  echo "Add NATIVEPHP_APP_ID to .env before native:install" >&2
  exit 1
fi

if ! php artisan list native 2>/dev/null | grep -q 'native:run'; then
  echo "NativePHP not installed. Run:" >&2
  echo "  composer install && php artisan native:install" >&2
  exit 1
fi

echo "=== Browser check (stop with Ctrl+C when you see the splash) ==="
echo "Run in another terminal if you want to verify first:"
echo "  cd $PROJECT && php artisan serve"
echo ""

case "$OS" in
  ios|i|android|a) ;;
  *)
    echo "Usage: $0 [ios|android]" >&2
    exit 1
    ;;
esac

echo "=== First simulator run: native:run $OS ==="
echo "Project: $PROJECT"
echo "Expected on screen: It works on mobile"
echo ""

php artisan native:run "$OS"
