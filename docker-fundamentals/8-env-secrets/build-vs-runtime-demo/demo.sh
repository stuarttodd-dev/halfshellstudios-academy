#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo "=== Build-time: APP_VERSION in image layers ==="
docker build --build-arg APP_VERSION=1.0.0 -t ch8-config:v1 .
docker build --build-arg APP_VERSION=2.0.0 -t ch8-config:v2 .

echo ""
echo "v1 built-version.txt:"
docker run --rm ch8-config:v1 cat /built-version.txt
echo "v2 built-version.txt:"
docker run --rm ch8-config:v2 cat /built-version.txt

echo ""
echo "=== Run-time: same image, different env (no rebuild) ==="
docker run --rm ch8-config:v1 sh -c 'echo LOG_CHANNEL=$LOG_CHANNEL'
docker run --rm -e LOG_CHANNEL=stdout -e DB_HOST=db ch8-config:v1 sh -c 'echo LOG_CHANNEL=$LOG_CHANNEL DB_HOST=$DB_HOST'

echo ""
echo "=== Visibility: image ENV defaults and history ==="
docker image inspect ch8-config:v1 --format '{{ json .Config.Env }}'
echo ""
docker history ch8-config:v1 | head -8
