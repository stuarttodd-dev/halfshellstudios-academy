#!/usr/bin/env bash
set -euo pipefail

docker rm -f ch6-web 2>/dev/null || true
docker network rm ch6-dns-net 2>/dev/null || true

echo "Removed ch6-web and ch6-dns-net."
