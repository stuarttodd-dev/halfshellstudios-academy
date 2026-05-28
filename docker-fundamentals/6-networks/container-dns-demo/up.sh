#!/usr/bin/env bash
set -euo pipefail

docker network create ch6-dns-net 2>/dev/null || true
docker rm -f ch6-web 2>/dev/null || true
docker run -d --name ch6-web --network ch6-dns-net nginx:stable-alpine

echo "Network ch6-dns-net ready; ch6-web is running."
