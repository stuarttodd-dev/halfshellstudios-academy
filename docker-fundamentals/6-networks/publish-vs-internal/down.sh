#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose -f compose.ports-demo.yaml down "$@"
echo "Stack ch6-ports-demo stopped."
