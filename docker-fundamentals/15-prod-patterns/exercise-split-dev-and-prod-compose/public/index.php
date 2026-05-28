<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
$debug = getenv('APP_DEBUG') ?: 'unset';
echo "ch15 split exercise ok APP_DEBUG={$debug}\n";
