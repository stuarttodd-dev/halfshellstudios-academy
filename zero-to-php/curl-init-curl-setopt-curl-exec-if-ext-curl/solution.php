<?php
declare(strict_types=1);

$ch = curl_init('https://example.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

echo is_resource($ch) || $ch instanceof CurlHandle ? 'curl' : 'nocurl';
curl_close($ch);
