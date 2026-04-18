<?php
declare(strict_types=1);

$cipher = 'AES-128-CBC';
$key = '1234567890abcdef';
$iv = 'abcdef1234567890';

$encrypted = openssl_encrypt('hi', $cipher, $key, 0, $iv);
$decrypted = is_string($encrypted) ? openssl_decrypt($encrypted, $cipher, $key, 0, $iv) : false;

echo $decrypted === 'hi' ? 'ok' : 'no';
