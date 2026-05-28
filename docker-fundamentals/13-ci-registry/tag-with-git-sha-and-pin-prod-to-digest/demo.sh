#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

REG_PORT="${REG_PORT:-5001}"
REG="127.0.0.1:${REG_PORT}"
IMAGE="${REG}/ch13-app"
SHA_TAG=sha-demo1234

port_in_use() {
  local p="$1"
  if command -v nc >/dev/null 2>&1; then
    nc -z 127.0.0.1 "$p" 2>/dev/null
  else
    (echo >/dev/tcp/127.0.0.1/"${p}") >/dev/null 2>&1
  fi
}

if port_in_use "$REG_PORT"; then
  for try in 5002 5003 15001; do
    if ! port_in_use "$try"; then
      echo "Port ${REG_PORT} busy; using ${try}" >&2
      REG_PORT="$try"
      REG="127.0.0.1:${REG_PORT}"
      IMAGE="${REG}/ch13-app"
      break
    fi
  done
fi

docker rm -f ch13-reg 2>/dev/null || true
docker compose -f compose.prod.yaml down 2>/dev/null || true

echo "=== 1. Local registry + build v1 ==="
docker run -d --name ch13-reg -p "127.0.0.1:${REG_PORT}:5000" registry:2
sleep 2

docker build -t ch13-app:local .

echo ""
echo "=== 1b. One build, three tags (sha + branch + latest) ==="
docker tag ch13-app:local "${IMAGE}:${SHA_TAG}"
docker tag ch13-app:local "${IMAGE}:main"
docker tag ch13-app:local "${IMAGE}:latest"
docker push "${IMAGE}:${SHA_TAG}"
docker push "${IMAGE}:main"
docker push "${IMAGE}:latest"

echo ""
echo "=== 2. Prove tags share one digest ==="
digest_sha="$(docker buildx imagetools inspect "${IMAGE}:${SHA_TAG}" --format '{{json .Manifest.Digest}}' | tr -d '"')"
digest_main="$(docker buildx imagetools inspect "${IMAGE}:main" --format '{{json .Manifest.Digest}}' | tr -d '"')"
digest_latest="$(docker buildx imagetools inspect "${IMAGE}:latest" --format '{{json .Manifest.Digest}}' | tr -d '"')"
echo "sha=${digest_sha}"
echo "main=${digest_main}"
echo "latest=${digest_latest}"
[[ "$digest_sha" == "$digest_main" && "$digest_main" == "$digest_latest" ]]

echo ""
echo "=== 3. Production pull by digest only ==="
docker pull "${IMAGE}@${digest_sha}"
v1_out="$(docker run --rm "${IMAGE}@${digest_sha}")"
echo "$v1_out"
echo "$v1_out" | grep -q 'build=v1'

echo ""
echo "=== 4. Moving tag — :main gets v2; prod digest stays v1 ==="
cat > Dockerfile.v2 <<'EOF'
FROM alpine:3.20
RUN echo build=v2 > /build-id.txt
CMD ["cat", "/build-id.txt"]
EOF
docker build -f Dockerfile.v2 -t ch13-app:local .
docker tag ch13-app:local "${IMAGE}:main"
docker push "${IMAGE}:main"
docker pull "${IMAGE}:main"
main_out="$(docker run --rm "${IMAGE}:main")"
echo ":main now → $main_out"
echo "$main_out" | grep -q 'build=v2'
pinned_out="$(docker run --rm "${IMAGE}@${digest_sha}")"
echo "prod @digest still → $pinned_out"
echo "$pinned_out" | grep -q 'build=v1'

echo ""
echo "=== 5. Compose prod pin ==="
sed "s|127.0.0.1:5001/ch13-app@sha256:REPLACE_ME|${IMAGE}@${digest_sha}|" compose.prod.yaml \
  > compose.prod.generated.yaml
docker compose -f compose.prod.generated.yaml pull
compose_out="$(docker compose -f compose.prod.generated.yaml run --rm app)"
echo "$compose_out"
echo "$compose_out" | grep -q 'build=v1'

docker compose -f compose.prod.generated.yaml down 2>/dev/null || true
docker rm -f ch13-reg 2>/dev/null || true
rm -f compose.prod.generated.yaml Dockerfile.v2

echo ""
echo "Demo OK: three tags, one digest; prod pinned @sha256 survives :main drift."
