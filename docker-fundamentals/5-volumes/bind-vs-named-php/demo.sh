#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo "=== Bind mount: php -v (no host PHP required) ==="
docker run --rm \
  -v "$PWD":/app \
  -w /app \
  php:8.3-cli php -v | head -1

echo ""
echo "=== Run hello.php from the mounted project tree ==="
docker run --rm \
  -v "$PWD":/app \
  -w /app \
  php:8.3-cli php hello.php

echo ""
echo "=== Edit on host, rerun immediately (no image rebuild) ==="
printf '%s\n' '<?php echo "hello from mounted code (edited)\n";' > hello.php
docker run --rm \
  -v "$PWD":/app \
  -w /app \
  php:8.3-cli php hello.php

echo ""
echo "=== Laravel-shaped path (/var/www/html) ==="
docker run --rm \
  -v "$PWD":/var/www/html \
  -w /var/www/html \
  php:8.3-cli php bin/artisan-stub.php --version

echo ""
echo "=== Same bind mount with explicit --mount syntax ==="
docker run --rm \
  --mount type=bind,source="$PWD",target=/app,readonly \
  -w /app \
  php:8.3-cli php hello.php

# Restore starter file for the next run
printf '%s\n' '<?php' '' 'echo "hello from mounted code\n";' > hello.php

echo ""
echo "Demo OK — bind mount dev loop verified."
