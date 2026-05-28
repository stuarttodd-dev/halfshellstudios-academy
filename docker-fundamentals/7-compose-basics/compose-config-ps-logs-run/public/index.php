<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
echo "ch7 compose config / ps / logs / run demo\n";
echo 'DB_HOST=' . (getenv('DB_HOST') ?: '(unset)') . "\n";
