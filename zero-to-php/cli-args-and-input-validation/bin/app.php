<?php
declare(strict_types=1);

$command = $argv[1] ?? '';

if ($command === '') {
    fwrite(STDERR, "Usage: php bin/app.php [add|done]\n");
    exit(1);
}

if ($command === 'add') {
    $title = trim(implode(' ', array_slice($argv, 2)));
    if ($title === '') {
        fwrite(STDERR, "Error: add requires a non-empty task.\n");
        exit(1);
    }
    echo "Added: {$title}\n";
    exit(0);
}

if ($command === 'done') {
    if (!ctype_digit($argv[2] ?? '')) {
        fwrite(STDERR, "Error: done requires a numeric task id.\n");
        exit(1);
    }
    echo "Marked #{$argv[2]} as done.\n";
    exit(0);
}

fwrite(STDERR, "Unknown command: {$command}\n");
exit(1);
