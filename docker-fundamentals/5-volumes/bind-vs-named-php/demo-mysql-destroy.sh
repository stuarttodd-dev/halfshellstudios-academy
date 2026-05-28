#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE=(docker compose -f compose.mysql.yaml)
export MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-secret}"
export MYSQL_DATABASE="${MYSQL_DATABASE:-app}"

echo "=== docker compose down -v (removes named volume dbdata) ==="
"${COMPOSE[@]}" down -v

echo "Volume dbdata removed. Next 'up' starts with an empty database."
echo "Run ./demo-mysql.sh again to re-seed from scratch."
