#!/usr/bin/env bash
set -euo pipefail

docker rmi workdir-bad:0.1 workdir-good:0.1 2>/dev/null || true
echo "Removed workdir-bad:0.1 and workdir-good:0.1."
