<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$lines = [
    'ch11 proxy exercise ok',
    'HTTP_X_FORWARDED_PROTO=' . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '(unset)'),
    'HTTP_X_FORWARDED_HOST=' . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '(unset)'),
    'SCRIPT_FILENAME=' . ($_SERVER['SCRIPT_FILENAME'] ?? '(unset)'),
];

echo implode("\n", $lines) . "\n";
