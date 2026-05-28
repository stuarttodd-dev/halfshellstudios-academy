#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose -f compose.stack.yaml up -d "$@"
docker compose -f compose.stack.yaml ps
