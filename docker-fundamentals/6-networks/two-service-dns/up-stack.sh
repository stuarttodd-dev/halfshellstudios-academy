#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose -f compose.php-mysql-redis.yaml up -d
docker compose -f compose.php-mysql-redis.yaml ps
