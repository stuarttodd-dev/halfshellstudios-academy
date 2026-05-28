<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('ch9');
$log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
$log->info('multi-stage shrink demo');

header('Content-Type: text/plain; charset=utf-8');
echo "ch9 shrink demo — vendor from Composer\n";
