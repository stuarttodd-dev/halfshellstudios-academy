#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

port="${HTTP_PORT:-8080}"
url="${1:-http://127.0.0.1:${port}/}"

headers="$(curl -fsSI "$url")"
body="$(curl -fsS "$url")"

echo "$headers" | head -5
echo "---"
echo "$body"

echo "$body" | grep -qi 'FastCGI via'
echo "$body" | grep -q 'php_version='
echo "Smoke OK: $url"
