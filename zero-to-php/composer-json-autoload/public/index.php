<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Greeter;

$greeter = new Greeter('Composer autoload');
echo $greeter->greet() . PHP_EOL;
