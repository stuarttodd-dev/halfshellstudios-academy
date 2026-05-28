#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

CONTAINER="${MYSQL_CONTAINER:-my_mysql}"
VOLUME="${MYSQL_VOLUME:-mysql_data}"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

docker rm -f "$CONTAINER" 2>/dev/null || true
docker volume rm "$VOLUME" 2>/dev/null || true
echo "Removed ${CONTAINER} (if present) and volume ${VOLUME} (if present)."
