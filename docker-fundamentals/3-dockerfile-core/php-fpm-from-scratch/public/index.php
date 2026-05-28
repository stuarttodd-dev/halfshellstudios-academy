<?php

require dirname(__DIR__) . '/vendor/autoload.php';

header('Content-Type: text/plain');
echo App\Demo::message() . PHP_EOL;
