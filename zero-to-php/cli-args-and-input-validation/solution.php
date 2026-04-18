<?php
declare(strict_types=1);

$command = $argv[1] ?? '';

if ($command === 'add' && trim(implode(' ', array_slice($argv, 2))) === '') {
    fwrite(STDERR, "Error: add requires a non-empty task.\n");
    exit(1);
}

if ($command === 'done' && !ctype_digit($argv[2] ?? '')) {
    fwrite(STDERR, "Error: done requires a numeric task id.\n");
    exit(1);
}

echo "ok\n";
