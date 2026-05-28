<?php

declare(strict_types=1);

// Laravel equivalent: php artisan queue:work
require dirname(__DIR__) . '/bootstrap.php';

$redis = redisClient();
$queue = 'capstone:jobs';

fwrite(STDOUT, "worker listening on {$queue}\n");

while (true) {
    $job = $redis->blPop([$queue], 0);
    if (!is_array($job) || count($job) < 2) {
        continue;
    }

    fwrite(STDOUT, 'processed job: ' . $job[1] . PHP_EOL);
}
