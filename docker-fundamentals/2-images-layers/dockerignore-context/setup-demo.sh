#!/usr/bin/env bash
# Create local-only junk in the build context (not committed). Run from this folder.
set -euo pipefail

mkdir -p node_modules/demo-lib vendor/cache dist build coverage tmp .cache

# Simulate heavy dependency trees (macOS/Linux)
if dd if=/dev/zero of=node_modules/demo-lib/deps.bin bs=1m count=8 2>/dev/null; then
  :
else
  # Fallback when /dev/zero or count syntax differs
  python3 -c "open('node_modules/demo-lib/deps.bin','wb').write(b'0'*8*1024*1024)"
fi

if dd if=/dev/zero of=vendor/cache/packages.bin bs=1m count=5 2>/dev/null; then
  :
else
  python3 -c "open('vendor/cache/packages.bin','wb').write(b'0'*5*1024*1024)"
fi

echo 'DATABASE_URL=postgres://secret:password@db.example/internal' > .env
echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) demo log" > app.log

echo "Demo context bloat created. Run: du -sh ./* ./.[!.]* 2>/dev/null | sort -h"
