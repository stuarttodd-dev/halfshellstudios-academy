<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\CalculatorService;

$calculator = new CalculatorService();

echo $calculator->add(10, 3) . PHP_EOL;
echo $calculator->subtract(10, 3) . PHP_EOL;
echo $calculator->multiply(4, 5) . PHP_EOL;
echo $calculator->divide(20, 4) . PHP_EOL;

try {
    $calculator->divide(1, 0);
} catch (\InvalidArgumentException $e) {
    echo 'error: ' . $e->getMessage() . PHP_EOL;
}
