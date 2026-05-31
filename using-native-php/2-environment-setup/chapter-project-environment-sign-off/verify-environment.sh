#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
FAIL=0

pass() { echo "  OK  $1"; }
fail() { echo "  FAIL $1" >&2; FAIL=1; }
warn() { echo "  WARN $1" >&2; }

need_cmd() {
  if command -v "$1" >/dev/null 2>&1; then
    pass "$1 found ($(command -v "$1"))"
  else
    fail "$1 not found"
  fi
}

version_ge() {
  # usage: version_ge "8.3.0" "8.3"
  printf '%s\n%s\n' "$2" "$1" | sort -C -V 2>/dev/null
}

echo "=== NativePHP Mobile — environment sign-off checks ==="
echo ""

echo "-- Core toolchain --"
need_cmd php
need_cmd composer
need_cmd node

if command -v php >/dev/null 2>&1; then
  PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION.".".PHP_RELEASE_VERSION;')"
  if version_ge "$PHP_VER" "8.3.0"; then
    pass "PHP $PHP_VER (>= 8.3)"
  else
    fail "PHP $PHP_VER — need 8.3+"
  fi
fi

if command -v composer >/dev/null 2>&1; then
  pass "Composer $(composer -V 2>/dev/null | head -1)"
fi

if command -v node >/dev/null 2>&1; then
  NODE_VER="$(node -v | sed 's/^v//')"
  if version_ge "$NODE_VER" "20.0.0"; then
    pass "Node $NODE_VER (>= 20)"
  else
    warn "Node $NODE_VER — NativePHP recommends 20+"
  fi
fi

echo ""
echo "-- iOS simulator path (macOS) --"
if [[ "$(uname -s)" == "Darwin" ]]; then
  if xcode-select -p >/dev/null 2>&1; then
    pass "Xcode CLI: $(xcode-select -p)"
  else
    fail "Xcode command line tools — run: xcode-select --install"
  fi
  if command -v xcrun >/dev/null 2>&1 && xcrun simctl list devices available 2>/dev/null | grep -q iPhone; then
    pass "iOS Simulator runtimes available"
  else
    warn "No iPhone simulators listed — open Xcode once and install a simulator runtime"
  fi
else
  warn "Not macOS — skip iOS simulator; use Android or a physical device with Jump"
fi

echo ""
echo "-- Android (optional) --"
if command -v adb >/dev/null 2>&1; then
  pass "adb $(adb version 2>/dev/null | head -1)"
  adb devices 2>/dev/null | tail -n +2 | grep -q . && pass "adb sees a device/emulator" || warn "adb: no devices attached"
else
  warn "adb not in PATH — install Android Studio SDK if you target Android"
fi

echo ""
echo "-- Laravel + NativePHP project (optional) --"
PROJECT="${NATIVEPHP_PROJECT:-$ROOT/../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen}"
if [[ -f "$PROJECT/artisan" ]]; then
  pass "Project: $PROJECT"
  if [[ -f "$PROJECT/.env" ]] && grep -q '^NATIVEPHP_APP_ID=' "$PROJECT/.env" 2>/dev/null; then
    pass "NATIVEPHP_APP_ID set in .env"
  else
    warn "NATIVEPHP_APP_ID missing in $PROJECT/.env"
  fi
  if (cd "$PROJECT" && php artisan list native 2>/dev/null | grep -q 'native:run'); then
    pass "native:run command available"
  else
    warn "Run composer install + native:install in project first"
  fi
else
  warn "No Laravel project at $PROJECT — set NATIVEPHP_PROJECT or complete Chapter 1 first"
fi

echo ""
if [[ "$FAIL" -eq 0 ]]; then
  echo "Sign-off checks passed. Next: fill environment-sign-off.md and run ./run-ios-simulator.sh"
  exit 0
fi

echo "Fix failures above before signing off." >&2
exit 1
