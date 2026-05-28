<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$lines = [
    'HTTPS server var=' . ($_SERVER['HTTPS'] ?? '(unset)'),
    'REQUEST_SCHEME=' . ($_SERVER['REQUEST_SCHEME'] ?? '(unset)'),
    'HTTP_X_FORWARDED_PROTO=' . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '(unset)'),
    'HTTP_X_FORWARDED_HOST=' . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '(unset)'),
    'REMOTE_ADDR=' . ($_SERVER['REMOTE_ADDR'] ?? '(unset)'),
];
echo implode("\n", $lines) . "\n";
