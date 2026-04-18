<?php
declare(strict_types=1);

$command = $argv[1] ?? '';
$value = trim(implode(' ', array_slice($argv, 2)));

switch ($command) {
    case 'list':
        echo "No tasks yet.\n";
        break;
    case 'add':
        if ($value === '') {
            echo "Usage: php bin/app.php add \"Task\"\n";
            exit(1);
        }
        echo "Added: {$value}\n";
        break;
    default:
        echo "Usage: php bin/app.php [list|add \"Task\"]\n";
        exit(1);
}
