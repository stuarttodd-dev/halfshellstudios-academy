<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$dbHost = getenv('DB_HOST') ?: 'db';
$redisHost = getenv('REDIS_HOST') ?: 'redis';

echo "PHP app — internal hostnames (not 127.0.0.1)\n";
echo "DB_HOST={$dbHost}\n";
echo "REDIS_HOST={$redisHost}\n";
echo "db resolves: " . gethostbyname($dbHost) . "\n";
echo "redis resolves: " . gethostbyname($redisHost) . "\n";

$errno = 0;
$errstr = '';
$dbSock = @fsockopen($dbHost, 3306, $errno, $errstr, 3);
echo $dbSock ? "db:3306 reachable\n" : "db:3306 unreachable ({$errstr})\n";
if ($dbSock) {
    fclose($dbSock);
}

$redisSock = @fsockopen($redisHost, 6379, $errno, $errstr, 3);
echo $redisSock ? "redis:6379 reachable\n" : "redis:6379 unreachable ({$errstr})\n";
if ($redisSock) {
    fclose($redisSock);
}
