<?php

declare(strict_types=1);

file_put_contents(
    __DIR__ . '/../storage/logs/ch10-write-test.log',
    'written by ' . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'php') . PHP_EOL,
    FILE_APPEND
);
echo "ok\n";
