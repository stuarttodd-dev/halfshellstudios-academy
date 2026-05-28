<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$pdo = new PDO(
    'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
);
$row = $pdo->query('SELECT message FROM greetings ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);

$redis = new Redis();
$redis->connect(getenv('REDIS_HOST'), (int) (getenv('REDIS_PORT') ?: 6379));
$redis->set('capstone:probe', 'ok', 60);
$cache = $redis->get('capstone:probe');

echo 'mysql: ' . ($row['message'] ?? 'no row') . "\n";
echo 'redis: ' . $cache . "\n";
