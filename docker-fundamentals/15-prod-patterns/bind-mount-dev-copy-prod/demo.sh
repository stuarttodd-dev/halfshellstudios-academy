#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

PORT="${HTTP_PORT:-8080}"
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$PORT" 2>/dev/null; then
  PORT=18080
fi
export HTTP_PORT="$PORT"
COMPOSE_DEV=(docker compose -f compose.yaml -f compose.dev.yaml)
COMPOSE_PROD=(docker compose -f compose.yaml -f compose.prod.yaml)

printf '%s\n' '<?php' 'declare(strict_types=1);' '' \
  "header('Content-Type: text/plain; charset=utf-8');" \
  "echo \"version: initial\n\";" > public/index.php

"${COMPOSE_DEV[@]}" down 2>/dev/null || true
"${COMPOSE_PROD[@]}" down 2>/dev/null || true

echo "=== 1. Build and render configs ==="
"${COMPOSE_DEV[@]}" build -q

dev_mounts="$("${COMPOSE_DEV[@]}" config 2>/dev/null | grep -E 'source:|target:' || true)"
prod_mounts="$("${COMPOSE_PROD[@]}" config 2>/dev/null | grep -E 'source:.*public|target:.*public' || true)"

echo "$dev_mounts" | grep -q 'public' || { echo "Expected public bind mount in dev merge" >&2; exit 1; }
if [[ -n "$prod_mounts" ]]; then
  echo "Unexpected app source bind mount in prod merge:" >&2
  echo "$prod_mounts" >&2
  exit 1
fi
echo "OK: dev mounts ./public; prod merge has no app source bind mount"

fetch_body() {
  for _ in $(seq 1 45); do
    if body="$(curl -fsS "http://127.0.0.1:${PORT}/" 2>/dev/null)"; then
      printf '%s' "$body"
      return 0
    fi
    sleep 1
  done
  echo "HTTP not ready on port ${PORT}" >&2
  return 1
}

echo ""
echo "=== 2. Dev: live edit loop ==="
"${COMPOSE_DEV[@]}" up -d
body="$(fetch_body)"
echo "$body" | grep -q 'version: initial'

printf '%s\n' '<?php' 'declare(strict_types=1);' '' \
  "header('Content-Type: text/plain; charset=utf-8');" \
  "echo \"version: edited-live\n\";" > public/index.php
sleep 1

body="$(fetch_body)"
echo "$body" | grep -q 'version: edited-live'
echo "OK: host edit visible without rebuild"

echo ""
echo "=== 3. Prod merge: image-only code path ==="
"${COMPOSE_DEV[@]}" down
"${COMPOSE_PROD[@]}" up -d
body="$(fetch_body)"
echo "$body" | grep -q 'version: initial'
echo "OK: prod still serves image built before host edit (COPY artifact)"

"${COMPOSE_PROD[@]}" down

echo ""
echo "Demo OK: bind-mount in dev; copy-only in prod image."
