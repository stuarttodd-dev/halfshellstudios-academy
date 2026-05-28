#!/usr/bin/env bash
set -euo pipefail

NET=demo-net
NAME=web

docker network create "$NET" 2>/dev/null || true
docker rm -f "$NAME" 2>/dev/null || true
docker run -d --name "$NAME" --network "$NET" nginx:stable-alpine

echo "Network $NET ready; $NAME is running (no host port published)."
