#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose -f compose.prod.yaml down 2>/dev/null || true
docker compose -f compose.prod.generated.yaml down 2>/dev/null || true
docker rm -f ch13-reg 2>/dev/null || true
rm -f compose.prod.generated.yaml
echo "Removed ch13 tags demo registry and compose stack."
