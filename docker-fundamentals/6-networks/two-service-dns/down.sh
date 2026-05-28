#!/usr/bin/env bash
set -euo pipefail

docker rm -f web 2>/dev/null || true
docker network rm demo-net 2>/dev/null || true
echo "Removed web and demo-net."
