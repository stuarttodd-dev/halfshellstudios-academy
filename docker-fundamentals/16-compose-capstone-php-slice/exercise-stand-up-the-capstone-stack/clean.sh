#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

if [[ -f .env ]]; then
  docker compose -f compose.yaml -f compose.dev.yaml --env-file .env down -v --remove-orphans 2>/dev/null || true
else
  docker compose -f compose.yaml -f compose.dev.yaml down -v --remove-orphans 2>/dev/null || true
fi

echo "clean: stack torn down"
