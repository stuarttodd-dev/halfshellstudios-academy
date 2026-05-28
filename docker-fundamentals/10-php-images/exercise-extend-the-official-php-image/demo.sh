#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

DEPS_TAG=ch10-extend:deps
RELEASE_TAG=ch10-extend:release
CNAME=ch10-extend-test

docker rm -f "$CNAME" 2>/dev/null || true

echo "=== 1. Build deps stage ==="
docker build --target deps -t "$DEPS_TAG" .
docker run --rm "$DEPS_TAG" test -d /app/vendor
echo "deps vendor OK"

echo ""
echo "=== 2. Build release image ==="
docker build --target runtime -t "$RELEASE_TAG" .

echo ""
echo "=== 3. Extensions (10.1 checklist) ==="
mods="$(docker run --rm "$RELEASE_TAG" php -m)"
echo "$mods" | grep -E 'pdo_mysql|intl|gd|redis'
for ext in pdo_mysql intl gd redis; do
  echo "$mods" | grep -qx "$ext" || { echo "Missing extension: $ext" >&2; exit 1; }
done

echo ""
echo "=== 4. php-fpm -t, id, no composer, memory_limit ==="
docker run --rm "$RELEASE_TAG" php-fpm -t 2>&1 | grep -qi 'successful'
id_out="$(docker run --rm "$RELEASE_TAG" id)"
echo "$id_out"
echo "$id_out" | grep -q 'www-data'
composer_out="$(docker run --rm "$RELEASE_TAG" sh -c 'command -v composer || echo no composer')"
echo "$composer_out"
echo "$composer_out" | grep -qx 'no composer'
mem="$(docker run --rm "$RELEASE_TAG" php -r "echo ini_get('memory_limit');")"
echo "memory_limit=${mem}"
[[ "$mem" == "256M" ]]

echo ""
echo "=== 5. Application smoke ==="
app_out="$(docker run --rm "$RELEASE_TAG" php public/index.php)"
echo "$app_out"
echo "$app_out" | grep -q 'pdo_mysql:ok'
echo "$app_out" | grep -q 'intl:ok'
echo "$app_out" | grep -q 'gd:ok'
echo "$app_out" | grep -q 'redis:ok'
echo "$app_out" | grep -qv 'MISSING'

echo ""
echo "=== 6. zz-app.ini loaded ==="
ini_out="$(docker run --rm "$RELEASE_TAG" php --ini)"
echo "$ini_out" | grep -q 'zz-app.ini'

echo ""
echo "=== 7. FPM health (tier 2 ping) ==="
docker run -d --name "$CNAME" "$RELEASE_TAG" >/dev/null
health=""
for _ in $(seq 1 35); do
  health="$(docker inspect "$CNAME" --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}none{{end}}')"
  [[ "$health" == "healthy" ]] && break
  sleep 1
done
echo "Health.Status=${health}"
[[ "$health" == "healthy" ]]
ping_out="$(docker exec "$CNAME" sh -c \
  'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000')"
echo "$ping_out"
echo "$ping_out" | grep -qx pong
docker rm -f "$CNAME" >/dev/null

echo ""
echo "Solution demo OK: chapter 10 extend official PHP image."
