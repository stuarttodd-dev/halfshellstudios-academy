<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

echo "ch7 override demo\n";
echo 'APP_DEBUG=' . (getenv('APP_DEBUG') ?: '(unset)') . "\n";
echo 'DB_HOST=' . (getenv('DB_HOST') ?: '(unset)') . "\n";
