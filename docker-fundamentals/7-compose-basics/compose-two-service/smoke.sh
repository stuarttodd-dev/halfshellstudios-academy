#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

port="${HTTP_PORT:-8080}"
url="${1:-http://127.0.0.1:${port}/}"
body="$(curl -fsS "$url")"

echo "$body"
echo "$body" | grep -q 'nginx + PHP-FPM via Compose'
echo "$body" | grep -q 'php_version='
echo "Smoke OK: $url"
