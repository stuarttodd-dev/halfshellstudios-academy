#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG=ch14-harden-exercise:local
VOL=ch14-harden-vol
CNAME=ch14-ro-test

down_all() {
  docker compose down -v 2>/dev/null || true
  docker rm -f "$CNAME" 2>/dev/null || true
}

down_all

echo "=== 1. Build + non-root runtime (14.2) ==="
docker compose build app
docker compose up -d
sleep 2
id_out="$(docker compose exec -T app id)"
echo "$id_out"
echo "$id_out" | grep -q 'www-data'
echo "$id_out" | grep -q 'uid=33'

echo ""
echo "=== 2. Read-only root + writable islands (14.3, 14.5) ==="
docker compose exec -T app php-fpm -t 2>&1 | grep -qi 'successful'
ro="$(docker inspect "$(docker compose ps -q app)" --format '{{.HostConfig.ReadonlyRootfs}}')"
echo "ReadonlyRootfs=${ro}"
[[ "$ro" == "true" ]]
docker compose exec -T app sh -c 'touch /tmp/ch14 && echo tmp-ok'
docker compose exec -T app sh -c 'touch storage/logs/ch14.log && echo volume-ok'
etc_err="$(docker compose exec -T app sh -c 'touch /etc/ch14 2>&1' || true)"
echo "$etc_err"
echo "$etc_err" | grep -qi 'read-only'

echo ""
echo "=== 3. cap_drop ALL (14.4) ==="
caps="$(docker inspect "$(docker compose ps -q app)" --format '{{.HostConfig.CapDrop}}')"
echo "CapDrop=${caps}"
echo "$caps" | grep -qi 'ALL'
if docker compose exec -T app sh -c 'command -v ss >/dev/null && ss -lntp | grep -q 9000'; then
  echo "FPM listening on 9000 (ss)"
elif docker compose exec -T app sh -c 'command -v nc >/dev/null && nc -z 127.0.0.1 9000'; then
  echo "FPM listening on 9000 (nc)"
else
  docker compose ps
  docker compose exec -T app php-fpm -t 2>&1 | grep -qi 'successful'
  echo "OK: FPM up with cap_drop ALL (php-fpm -t; ss/nc not in slim image)"
fi

echo ""
echo "=== 4. Full inspect ==="
docker inspect "$(docker compose ps -q app)" \
  --format 'User={{.Config.User}} ReadonlyRootfs={{.HostConfig.ReadonlyRootfs}} CapDrop={{.HostConfig.CapDrop}}'

echo ""
echo "=== 5. docker run --read-only (14.6) ==="
docker volume create "$VOL" >/dev/null 2>&1 || true
docker run -d --name "$CNAME" \
  --read-only \
  --user 33:33 \
  --tmpfs /tmp:size=64m,mode=1777 \
  --tmpfs /var/run:size=8m,mode=755 \
  --tmpfs /var/www/html/bootstrap/cache:size=16m,uid=33,gid=33,mode=0755 \
  -v "${VOL}:/var/www/html/storage" \
  --cap-drop ALL \
  "$TAG" >/dev/null
sleep 3
docker exec "$CNAME" php-fpm -t 2>&1 | grep -qi 'successful'
ro_run="$(docker inspect "$CNAME" --format '{{.HostConfig.ReadonlyRootfs}}')"
echo "docker run ReadonlyRootfs=${ro_run}"
[[ "$ro_run" == "true" ]]
docker rm -f "$CNAME" >/dev/null

docker compose down -v

echo ""
echo "Solution demo OK: hardened PHP-FPM — www-data, read_only, tmpfs, volume, cap_drop."
