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

if [[ ! -f .env ]]; then
  cp .env.example .env
fi

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

if [[ ! -f docker/nginx/certs/dev.crt ]]; then
  mkdir -p docker/nginx/certs
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout docker/nginx/certs/dev.key \
    -out docker/nginx/certs/dev.crt \
    -subj "/CN=localhost"
fi

chmod +x artisan

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

if port_in_use "$HTTP_PORT"; then
  echo "No free HTTP port found (tried 8080, 18080, 8081, 9080)." >&2
  exit 1
fi

restore_ports() {
  if [[ -f compose.yaml.bak ]]; then
    mv compose.yaml.bak compose.yaml
  fi
  if [[ -f compose.dev.yaml.bak ]]; then
    mv compose.dev.yaml.bak compose.dev.yaml
  fi
}

if [[ "$HTTP_PORT" != "8080" ]]; then
  cp compose.yaml compose.yaml.bak
  cp compose.dev.yaml compose.dev.yaml.bak
  trap restore_ports EXIT
  sed "s/127.0.0.1:8080:80/127.0.0.1:${HTTP_PORT}:80/" compose.yaml.bak > compose.yaml
  sed "s/127.0.0.1:8443:443/127.0.0.1:${HTTPS_PORT}:443/" compose.dev.yaml.bak > compose.dev.yaml
fi

export HTTP_PORT
make smoke
