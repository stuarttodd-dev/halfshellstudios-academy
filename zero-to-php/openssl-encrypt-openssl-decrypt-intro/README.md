# OpenSSL encrypt/decrypt intro

Basic round-trip solution for `openssl-encrypt-openssl-decrypt-intro`.

```php
<?php
declare(strict_types=1);

$cipher = 'AES-128-CBC';
$key = '1234567890abcdef';
$iv = 'abcdef1234567890';

$encrypted = openssl_encrypt('hi', $cipher, $key, 0, $iv);
$decrypted = is_string($encrypted) ? openssl_decrypt($encrypted, $cipher, $key, 0, $iv) : false;

echo $decrypted === 'hi' ? 'ok' : 'no';
```

Expected output: `ok`.

## Solution walkthrough

This uses one fixed cipher (`AES-128-CBC`), key, and IV to do a tiny encrypt/decrypt round trip.  
It encrypts `hi`, decrypts the encrypted value, and compares the decrypted text to the original input.

## How to test

1. Save the snippet as `solution.php` in this folder.
2. Run:
   ```bash
   php solution.php
   ```
3. Confirm the output is exactly `ok`.

← [Zero to PHP](../README.md)
