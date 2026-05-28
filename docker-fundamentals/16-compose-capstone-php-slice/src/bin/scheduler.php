<?php

declare(strict_types=1);

// Laravel equivalent: php artisan schedule:work
require dirname(__DIR__) . '/bootstrap.php';

$redis = redisClient();
$queue = 'capstone:jobs';

fwrite(STDOUT, "scheduler running (enqueue every 60s)\n");

while (true) {
    $payload = json_encode(['enqueued_at' => time()], JSON_THROW_ON_ERROR);
    $redis->rPush($queue, $payload);
    fwrite(STDOUT, "scheduled job: {$payload}\n");
    sleep(60);
}
