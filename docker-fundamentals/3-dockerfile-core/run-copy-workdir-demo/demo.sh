#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo "=== Sloppy: split apt RUN, scattered paths, ADD ==="
docker build -f Dockerfile.bad -t workdir-bad:0.1 .
echo ""
docker image history workdir-bad:0.1 --no-trunc | head -10 || true

echo ""
echo "=== Disciplined: WORKDIR, chained apt RUN, COPY ==="
docker build -t workdir-good:0.1 .
echo ""
docker image history workdir-good:0.1 | head -10 || true

echo ""
echo "=== Run good image (CMD php -v) ==="
docker run --rm workdir-good:0.1
