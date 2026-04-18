<?php
declare(strict_types=1);

$lines = [
    'HTTP/1.1 302 Found',
    'Content-Type: application/json',
    'Location: /login',
];

echo str_contains($lines[0], '302') && str_contains($lines[2], '/login') ? 'headers' : 'no';
