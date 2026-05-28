<?php

declare(strict_types=1);

namespace App;

final class Demo
{
    public static function message(): string
    {
        $checks = [
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'intl' => extension_loaded('intl'),
            'gd' => extension_loaded('gd'),
            'redis' => extension_loaded('redis'),
        ];
        $parts = [];
        foreach ($checks as $name => $ok) {
            $parts[] = $name . ($ok ? ':ok' : ':MISSING');
        }

        return 'ch10 exercise: ' . implode(', ', $parts);
    }
}
