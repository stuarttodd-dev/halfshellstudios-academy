#!/usr/bin/env bash
set -euo pipefail

echo "=== On-network lookup (should resolve ch6-web) ==="
docker run --rm --network ch6-dns-net alpine getent hosts ch6-web

echo ""
echo "=== BusyBox nslookup (same network) ==="
docker run --rm --network ch6-dns-net busybox nslookup ch6-web

echo ""
echo "=== Off-network lookup (should fail) ==="
if docker run --rm alpine getent hosts ch6-web 2>/dev/null; then
  echo "Unexpected: resolved off-network"
  exit 1
else
  echo "(expected: no useful result off ch6-dns-net)"
fi
