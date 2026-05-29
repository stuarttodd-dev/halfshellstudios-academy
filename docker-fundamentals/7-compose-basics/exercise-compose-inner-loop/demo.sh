#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

port_in_use() {
  local p="$1"
  if command -v lsof >/dev/null 2>&1; then
    lsof -nP -iTCP:"${p}" -sTCP:LISTEN >/dev/null 2>&1
  elif command -v nc >/dev/null 2>&1; then
    nc -z 127.0.0.1 "$p" 2>/dev/null
  else
    (echo >/dev/tcp/127.0.0.1/"${p}") >/dev/null 2>&1
  fi
}

pick_port() {
  local want="${HTTP_PORT:-8080}"
  if ! port_in_use "$want"; then
    echo "$want"
    return
  fi
  for try in 8081 8082 18080 28080; do
    if ! port_in_use "$try"; then
      echo "Port ${want} busy; using ${try}" >&2
      echo "$try"
      return
    fi
  done
  echo "No free port found (tried ${want} and fallbacks). Set HTTP_PORT in .env" >&2
  exit 1
}

export HTTP_PORT="$(pick_port)"
BASE_URL="http://127.0.0.1:${HTTP_PORT}/"

compose_top() {
  local svc="$1"
  if docker compose top "$svc" 2>/dev/null | grep -q .; then
    docker compose top "$svc" | head -8
    return
  fi
  local cid
  cid="$(docker compose ps -q "$svc" 2>/dev/null || true)"
  if [[ -n "$cid" ]]; then
    docker top "$cid" | head -8
    return
  fi
  echo "Could not list processes for $svc" >&2
  exit 1
}

wait_for_running() {
  local svc="$1"
  for _ in $(seq 1 60); do
    docker compose ps --status running --format '{{.Service}}' | grep -qx "$svc" && return 0
    sleep 2
  done
  echo "Timed out waiting for $svc to be running" >&2
  docker compose ps -a >&2 || true
  exit 1
}

./clean.sh 2>/dev/null || true

echo "=== 1. Validate merged config (exercise task 1) ==="
docker compose config --quiet && echo "config OK"
services="$(docker compose config --services | tr '\n' ' ')"
echo "services: $services"
echo "$services" | grep -q web
echo "$services" | grep -q php
echo "$services" | grep -q db

echo ""
echo "=== 2. Start stack and inspect state (exercise task 2) ==="
docker compose up -d
wait_for_running web
wait_for_running php
wait_for_running db
docker compose ps
docker compose ps --status running --format '{{.Service}}' | grep -qx web
docker compose ps --status running --format '{{.Service}}' | grep -qx php
docker compose ps --status running --format '{{.Service}}' | grep -qx db
docker compose ps | grep -q "${HTTP_PORT}->80"

echo ""
echo "=== 3. HTTP smoke test (exercise task 3) ==="
code="$(curl -sS -o /dev/null -w '%{http_code}' "$BASE_URL")"
echo "http_code=$code"
case "$code" in
  200|301|302) ;;
  *) echo "Unexpected HTTP status: $code" >&2; exit 1 ;;
esac
curl -fsSI "$BASE_URL" | head -1

echo ""
echo "=== 4. Focused logs (exercise task 4) ==="
docker compose logs --tail=15 web
docker compose logs --tail=15 php

echo ""
echo "=== 5. Processes inside (exercise task 5) ==="
echo "--- web ---"
compose_top web
echo "--- php ---"
compose_top php

echo ""
echo "=== 6. run --rm vs exec (exercise tasks 6–7) ==="
docker compose run --rm php php -r 'echo "one-off ok\n";' | grep -q 'one-off ok'
docker compose exec -T php php -r 'echo "exec ok\n";' | grep -q 'exec ok'

echo ""
echo "=== 7. Stopped container visibility (exercise task 8) ==="
docker compose stop php
docker compose ps || true
docker compose ps -a | grep -qi php
docker compose start php
wait_for_running php
docker compose ps --status running --format '{{.Service}}' | grep -qx php

echo ""
echo "=== 8. Tear down (exercise task 9) ==="
docker compose down
if docker compose ps -a --format '{{.Service}}' | grep -qE '^(web|php|db)$'; then
  echo "Project containers still listed after down" >&2
  docker compose ps -a >&2
  exit 1
fi

echo ""
echo "Demo OK: compose inner loop (config → up → curl → logs → top → run/exec → stop → down)."
