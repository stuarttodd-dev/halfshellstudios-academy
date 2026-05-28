<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

echo "nginx + PHP-FPM via Compose\n";
echo 'php_version=' . PHP_VERSION . "\n";
echo 'hostname=' . gethostname() . "\n";
