#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose down -v 2>/dev/null || true
docker compose -f compose.starter.yaml down -v 2>/dev/null || true
docker rm -f ch14-ro-test 2>/dev/null || true
docker volume rm ch14-harden-vol 2>/dev/null || true
echo "Removed ch14 harden exercise stack."
