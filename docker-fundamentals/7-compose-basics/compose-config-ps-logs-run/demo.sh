#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

pick_port() {
  local p="${HTTP_PORT:-8080}"
  if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$p" 2>/dev/null; then
    echo 18080
  else
    echo "$p"
  fi
}
export HTTP_PORT="$(pick_port)"

compose_top() {
  local svc="${1:-}"
  local cid
  if [[ -n "$svc" ]]; then
    cid="$(docker compose ps -q "$svc" 2>/dev/null || true)"
    [[ -n "$cid" ]] && docker top "$cid" | head -8
    return 0
  fi
  for s in web php db; do
    cid="$(docker compose ps -q "$s" 2>/dev/null || true)"
    if [[ -n "$cid" ]]; then
      echo "# $s"
      docker top "$cid" | head -4
    fi
  done
}

echo "=== 1. Render and validate (config) ==="
docker compose config --quiet && echo "config OK"
echo "services: $(docker compose config --services | tr '\n' ' ')"
docker compose config --volumes | grep -q dbdata
docker compose config | grep -q 'APP_DEBUG: "true"'
docker compose config | grep -q '/var/www/html'
docker compose config | grep -q 'DB_HOST: db'
echo "OK: merged override shows bind mount + base DB_HOST"

echo ""
echo "=== 2. Start stack ==="
docker compose down -v 2>/dev/null || true
docker compose up -d --build
sleep 3
docker compose ps
docker compose ps --status running --format '{{.Service}}' | grep -qx web
docker compose ps --status running --format '{{.Service}}' | grep -qx php
docker compose ps | grep -q "${HTTP_PORT}->80"

echo ""
echo "=== 3. Processes inside (top) ==="
compose_top
echo "--- php ---"
compose_top php

echo ""
echo "=== 4. Focused logs ==="
docker compose logs --tail=5 web
docker compose logs --tail=5 php

echo ""
echo "=== 5. One-off run --rm ==="
docker compose run --rm php php -r 'echo "one-off ok\n";'
if docker compose ps -a --format '{{.Service}} {{.State}}' | grep -qE '^php-run '; then
  echo "Note: ephemeral run container may appear briefly in ps -a" >&2
fi
docker compose ps --status running --format '{{.Service}}' | grep -qx php

echo ""
echo "=== 6. exec vs run ==="
docker compose exec -T php php -r 'echo "exec in running container\n";'

echo ""
echo "=== 7. Stopped container visibility ==="
docker compose stop php
docker compose ps || true
docker compose ps -a | grep -qi php
docker compose start php
for _ in $(seq 1 15); do
  docker compose ps --status running --format '{{.Service}}' | grep -qx php && break
  sleep 1
done
docker compose ps --status running --format '{{.Service}}' | grep -qx php

docker compose down -v

echo ""
echo "Demo OK: config → ps → top → logs → run --rm debugging ladder."
