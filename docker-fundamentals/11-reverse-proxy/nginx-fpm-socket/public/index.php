<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$via = getenv('FASTCGI_VIA') ?: 'tcp';
echo 'FastCGI via ' . $via . "\n";
echo "nginx reverse proxy → PHP-FPM (Compose)\n";
echo 'php_version=' . PHP_VERSION . "\n";
echo 'fpm_host=' . gethostname() . "\n";
