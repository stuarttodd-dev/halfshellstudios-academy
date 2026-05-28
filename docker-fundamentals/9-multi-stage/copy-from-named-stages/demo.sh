#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

RT=ch9-named:runtime
BUILD=ch9-named:build-only
NUMERIC=ch9-named:numeric

echo "=== 1. Build runtime stage (last stage in Dockerfile) ==="
docker build -t "$RT" .

echo ""
echo "=== 2. Run — expect v1 artifact ==="
out="$(docker run --rm "$RT")"
echo "$out"
echo "$out" | grep -q 'v1 artifact'

echo ""
echo "=== 3. Build tools did not copy forward ==="
gcc_check="$(docker run --rm "$RT" sh -c 'command -v gcc || echo "no gcc in runtime"')"
echo "$gcc_check"
echo "$gcc_check" | grep -q 'no gcc in runtime'

echo ""
echo "=== 4. Build intermediate stage with --target ==="
docker build --target build -t "$BUILD" -f Dockerfile .

rt_bytes="$(docker image inspect "$RT" --format '{{.Size}}')"
build_bytes="$(docker image inspect "$BUILD" --format '{{.Size}}')"
echo "Image sizes: runtime=${rt_bytes} bytes, build-only=${build_bytes} bytes"
if [[ "$rt_bytes" -ge "$build_bytes" ]]; then
  echo "Expected runtime image smaller than build-only stage" >&2
  exit 1
fi

docker run --rm "$BUILD" sh -c 'command -v gcc && test -f /out/release.txt'

echo ""
echo "=== 5. Explicit --target runtime (production habit) ==="
docker build --target runtime -t "${RT}-explicit" .
docker run --rm "${RT}-explicit" | grep -q 'v1 artifact'

echo ""
echo "=== 6. Numeric COPY --from=0 (works but fragile) ==="
docker build -f Dockerfile.numeric -t "$NUMERIC" .
docker run --rm "$NUMERIC" | grep -q 'v1 artifact'

echo ""
echo "Demo OK: named stages, COPY --from=build, --target build, runtime has no gcc."
echo "Run ./clean.sh to remove images."
