<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json');

$noteCount = (int) pdo()->query('SELECT COUNT(*) FROM notes')->fetchColumn();
$redis = redisClient();
$redis->set('capstone:last_web_hit', (string) time());

echo json_encode([
    'service' => 'capstone-app',
    'env' => env('APP_ENV', 'local'),
    'notes' => $noteCount,
    'redis' => $redis->ping() ? 'PONG' : 'FAIL',
], JSON_THROW_ON_ERROR);
