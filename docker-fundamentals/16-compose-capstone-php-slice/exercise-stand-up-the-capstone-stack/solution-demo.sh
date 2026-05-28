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

echo "=== Step 1: prepare tree ==="
mkdir -p storage bootstrap/cache app/storage docker/nginx/certs
cp -n .env.example .env 2>/dev/null || true
if [[ ! -f composer.lock ]]; then
  if command -v composer >/dev/null 2>&1; then
    composer update --no-install --no-interaction
  else
    docker run --rm -v "$ROOT:/app" -w /app composer:2 update --no-install --no-interaction
  fi
fi
if [[ ! -d vendor ]]; then
  if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --no-interaction
  else
    docker run --rm -v "$ROOT:/app" -w /app composer:2 install --no-dev --no-interaction
  fi
fi
chmod +x artisan

if [[ ! -f docker/nginx/certs/dev.crt ]]; then
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout docker/nginx/certs/dev.key \
    -out docker/nginx/certs/dev.crt \
    -subj "/CN=localhost"
fi

echo ""
echo "=== Step 2: build runtime image for prod merge ==="
docker build --target runtime -f docker/php/Dockerfile -t capstone-php-slice:release .

echo ""
echo "=== Step 3: validate compose merges ==="
docker compose -f compose.yaml -f compose.dev.yaml --env-file .env config --services
make ci-compose
if docker compose -f compose.yaml -f compose.prod.yaml --env-file .env.prod.example config \
  | grep -iE 'APP_DEBUG.*true|XDEBUG|source: \.' >/dev/null; then
  echo "FAIL: prod merge leaks dev settings" >&2
  exit 1
fi
echo "prod merge clean"

HTTP_PORT=8080
HTTPS_PORT=8443
if port_in_use "$HTTP_PORT"; then
  for try in 18080 8081 9080; do
    if ! port_in_use "$try"; then
      echo "Port ${HTTP_PORT} busy; using ${try}" >&2
      HTTP_PORT="$try"
      HTTPS_PORT=$((8443 + HTTP_PORT - 8080))
      break
    fi
  done
fi

restore_ports() {
  [[ -f compose.yaml.bak ]] && mv compose.yaml.bak compose.yaml
  [[ -f compose.dev.yaml.bak ]] && mv compose.dev.yaml.bak compose.dev.yaml
}

if [[ "$HTTP_PORT" != "8080" ]]; then
  cp compose.yaml compose.yaml.bak
  cp compose.dev.yaml compose.dev.yaml.bak
  trap restore_ports EXIT
  sed "s/127.0.0.1:8080:80/127.0.0.1:${HTTP_PORT}:80/" compose.yaml.bak > compose.yaml
  sed "s/127.0.0.1:8443:443/127.0.0.1:${HTTPS_PORT}:443/" compose.dev.yaml.bak > compose.dev.yaml
fi

echo ""
echo "=== Step 4: fresh database volume ==="
docker compose -f compose.yaml -f compose.dev.yaml --env-file .env down -v --remove-orphans 2>/dev/null || true

echo ""
echo "=== Step 5: make smoke ==="
export HTTP_PORT
make smoke

echo ""
echo "=== Step 6: optional HTTPS ==="
curl -kfsS "https://127.0.0.1:${HTTPS_PORT}/healthz.php"
echo ""

echo ""
echo "=== Step 8: clean up ==="
make down

echo ""
echo "solution-demo: all checks passed"
