<?php

declare(strict_types=1);

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class Demo
{
    public static function message(): string
    {
        $log = new Logger('ch9');
        $log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
        $log->info('multi-stage exercise booted');

        $pdo = extension_loaded('pdo_mysql') ? 'pdo_mysql ok' : 'pdo_mysql MISSING';

        return "ch9 exercise: {$pdo}, monolog ok";
    }
}
