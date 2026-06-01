#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

fail=0

check() {
  if "$@"; then
    echo "  OK   $*"
  else
    echo "  FAIL $*"
    fail=1
  fi
}

echo "Field Notes release candidate checks"
echo ""

if [[ ! -f .env ]]; then
  echo "  WARN .env missing — copy from .env.example"
else
  # shellcheck disable=SC1091
  source .env 2>/dev/null || true
fi

check test -f public/icon.png
check test -f public/splash.png
check test -f database/database.sqlite || test -f .env.example

check grep -q 'NATIVEPHP_APP_VERSION=' .env.example
check grep -q 'NATIVEPHP_APP_VERSION_CODE=' .env.example
check grep -q 'NATIVEPHP_APP_ID=com.halfshell.fieldnotes' .env.example
check grep -q 'FIELD_NOTES_PRIVACY_POLICY_URL=' .env.example
check grep -q 'FIREBASE_CREDENTIALS' config/nativephp.php

if [[ -f .env ]]; then
  if [[ "${APP_DEBUG:-true}" == "true" ]]; then
    echo "  WARN APP_DEBUG=true — set false before store release builds"
  else
    echo "  OK   APP_DEBUG=false"
  fi

  if [[ -z "${NATIVEPHP_APP_VERSION:-}" ]]; then
    echo "  FAIL NATIVEPHP_APP_VERSION not set in .env"
    fail=1
  else
    echo "  OK   NATIVEPHP_APP_VERSION=${NATIVEPHP_APP_VERSION}"
  fi
fi

if [[ ! -d vendor ]]; then
  echo "  WARN vendor/ missing — run ./setup.sh"
else
  echo ""
  echo "Running PHPUnit..."
  php artisan test -q && echo "  OK   php artisan test" || { echo "  FAIL php artisan test"; fail=1; }
fi

echo ""
if [[ "$fail" -eq 0 ]]; then
  echo "Release candidate checks passed."
  echo "Next: php artisan native:release ios | android"
  exit 0
fi

echo "Fix failures before creating store builds."
exit 1
