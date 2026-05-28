#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose -f compose.stack.yaml --profile mail up -d "$@"
docker compose -f compose.stack.yaml --profile mail ps
