<?php

declare(strict_types=1);

foreach (['APP_ENV', 'APP_DEBUG', 'DB_HOST', 'DB_DATABASE', 'QUEUE_CONNECTION'] as $key) {
    $v = getenv($key);
    echo $key . '=' . ($v === false ? '(unset)' : $v) . PHP_EOL;
}
