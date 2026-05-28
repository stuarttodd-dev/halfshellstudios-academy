<?php

declare(strict_types=1);

function env(string $key, ?string $default = null): string
{
    $value = getenv($key);
    if ($value === false) {
        if ($default === null) {
            throw new RuntimeException("Missing env: {$key}");
        }
        return $default;
    }
    return $value;
}

function pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST', 'db'),
        env('DB_PORT', '3306'),
        env('DB_DATABASE', 'capstone'),
    );

    $pdo = new PDO($dsn, env('DB_USERNAME', 'capstone'), env('DB_PASSWORD', 'capstone'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    return $pdo;
}

function redisClient(): Redis
{
    static $redis = null;
    if ($redis instanceof Redis) {
        return $redis;
    }

    $redis = new Redis();
    $redis->connect(env('REDIS_HOST', 'redis'), (int) env('REDIS_PORT', '6379'));

    return $redis;
}
