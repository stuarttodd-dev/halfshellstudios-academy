#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

port_in_use() {
  local p="$1"
  if command -v nc >/dev/null 2>&1; then
    nc -z 127.0.0.1 "$p" 2>/dev/null
  else
    (echo >/dev/tcp/127.0.0.1/"${p}") >/dev/null 2>&1
  fi
}

restore_ports() {
  [[ -f compose.yaml.bak ]] && mv compose.yaml.bak compose.yaml
}

HTTP_PORT=8080
if port_in_use "$HTTP_PORT"; then
  for try in 18080 8081 9080; do
    if ! port_in_use "$try"; then
      echo "Port ${HTTP_PORT} busy; using ${try}" >&2
      HTTP_PORT="$try"
      break
    fi
  done
fi

if [[ "$HTTP_PORT" != "8080" ]]; then
  cp compose.yaml compose.yaml.bak
  sed "s/127.0.0.1:8080:80/127.0.0.1:${HTTP_PORT}:80/" compose.yaml.bak > compose.yaml
fi

finish() {
  make down 2>/dev/null || true
  restore_ports
}

trap finish EXIT

export HTTP_PORT
COMPOSE_DEV="docker compose -f compose.yaml -f compose.dev.yaml --env-file .env"
COMPOSE_PROD="docker compose -f compose.yaml -f compose.prod.yaml --env-file .env.prod.example"

cp -n .env.example .env 2>/dev/null || true

cat > public/index.php <<'PHP'
<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
$debug = getenv('APP_DEBUG') ?: 'unset';
echo "ch15 split exercise ok APP_DEBUG={$debug}\n";
PHP
cp public/index.php public/index.php.bak

echo "=== Step 1: build runtime and dev images ==="
docker build --target runtime -t ch15-split-exercise:release .
docker build --target dev -t ch15-split-exercise:dev .

docker run --rm ch15-split-exercise:release php -m | grep -i xdebug && exit 1 || echo "runtime: no xdebug"
docker run --rm ch15-split-exercise:dev php -m | grep -i xdebug

echo ""
echo "=== Step 2: base compose is prod-safe ==="
grep -n '\.:/var/www\|XDEBUG' compose.yaml && exit 1 || echo "base clean"
grep -q '^  web:' compose.yaml
grep -q '^  app:' compose.yaml
echo "services: web app"

echo ""
echo "=== Step 3: dev stack + bind mount ==="
make down 2>/dev/null || true
make dev
sleep 3
curl -fsS "http://127.0.0.1:${HTTP_PORT}/" | grep -q 'APP_DEBUG=true'
$COMPOSE_DEV exec -T app php -m | grep -i xdebug

printf '%s\n' '<?php echo "edited\n";' > public/index.php
curl -fsS "http://127.0.0.1:${HTTP_PORT}/" | grep -q '^edited$'
cp public/index.php.bak public/index.php

echo ""
echo "=== Step 4: validate prod merge ==="
make ci-check
if $COMPOSE_PROD config | grep -iE 'APP_DEBUG.*true|XDEBUG|source: \.' >/dev/null; then
  echo "FAIL: prod merge leaks dev settings" >&2
  exit 1
fi
echo "prod merge clean"
$COMPOSE_PROD config | grep -q 'image: ch15-split-exercise:release'

echo ""
echo "=== Step 5: prod-shaped stack ==="
make down
make prod-up
sleep 3
curl -fsS "http://127.0.0.1:${HTTP_PORT}/" | grep -q 'APP_DEBUG=false'
$COMPOSE_PROD exec -T app php -m | grep -i xdebug && exit 1 || echo "prod: no xdebug"
$COMPOSE_PROD exec -T app printenv APP_DEBUG | grep -qx false
ro="$(docker inspect "$($COMPOSE_PROD ps -q app)" --format '{{.HostConfig.ReadonlyRootfs}}')"
[[ "$ro" == "true" ]]
echo "ReadonlyRootfs=true"

echo ""
echo "=== Step 6: README + Makefile alignment ==="
grep -q 'compose.yaml' README.md
make prod-config >/dev/null

echo ""
echo "=== Step 8: clean up ==="
trap - EXIT
finish

echo "demo: all checks passed"
