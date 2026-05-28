<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json');

try {
    pdo()->query('SELECT 1');
    $redisOk = redisClient()->ping();
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit(1);
}

if ($redisOk !== true && $redisOk !== '+PONG') {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'redis not reachable']);
    exit(1);
}

echo json_encode(['status' => 'ok']);
