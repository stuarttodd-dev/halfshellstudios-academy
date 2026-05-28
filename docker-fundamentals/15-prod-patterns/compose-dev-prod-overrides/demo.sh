#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE_DEV=(docker compose -f compose.yaml -f compose.dev.yaml)
COMPOSE_PROD=(docker compose -f compose.yaml -f compose.prod.yaml)
PORT="${HTTP_PORT:-8080}"

./down.sh 2>/dev/null || true

echo "=== 1. Same service graph (dev vs prod merge) ==="
dev_svc="$("${COMPOSE_DEV[@]}" config --services | tr '\n' ' ')"
prod_svc="$("${COMPOSE_PROD[@]}" config --services | tr '\n' ' ')"
echo "dev:  $dev_svc"
echo "prod: $prod_svc"

echo ""
echo "=== 2. Dev adds html mount; prod adds read_only ==="
if "${COMPOSE_DEV[@]}" config | grep -q '/usr/share/nginx/html'; then
  echo "OK: dev merge includes web html bind mount"
fi
if "${COMPOSE_PROD[@]}" config | grep -q 'read_only: true'; then
  echo "OK: prod merge includes read_only"
fi
if ! "${COMPOSE_PROD[@]}" config 2>/dev/null | grep -q '\./html:/usr/share/nginx/html'; then
  echo "OK: prod merge has no dev html bind mount on web"
fi

echo ""
echo "=== 3. Dev stack: live edit via bind mount ==="
echo '<h1>ch15 dev</h1>' > html/index.html
"${COMPOSE_DEV[@]}" up -d --build
sleep 3
curl -fsS "http://127.0.0.1:${PORT}/" | grep -q 'ch15 dev'
echo '<h1>edited live</h1>' > html/index.html
curl -fsS "http://127.0.0.1:${PORT}/" | grep -q 'edited live'
echo "OK: host html edit visible without rebuild"

echo ""
echo "=== 4. Prod config render (build local prod-demo image) ==="
docker build -t ch15-app:prod-demo . -q
"${COMPOSE_PROD[@]}" config >/dev/null
echo "OK: prod file stack renders with docker compose config"

echo ""
echo "Demo OK. Run ./down.sh when finished."
