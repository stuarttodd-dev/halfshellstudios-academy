#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose down -v 2>/dev/null || true
docker rm -f ch10-fpm-health-test 2>/dev/null || true
docker rmi ch10-fpm-health:local 2>/dev/null || true
echo "Removed ch10-fpm-health demo resources."
