# curl_init / curl_setopt / curl_exec

Basic solution for `curl-init-curl-setopt-curl-exec-if-ext-curl`.

```php
<?php
declare(strict_types=1);

$ch = curl_init('https://example.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

echo is_resource($ch) || $ch instanceof CurlHandle ? 'curl' : 'nocurl';
curl_close($ch);
```

Expected output: `curl`.

## Solution walkthrough

This creates a cURL handle, sets `CURLOPT_RETURNTRANSFER` and `CURLOPT_TIMEOUT`, then checks that a valid cURL handle exists.  
It closes the handle at the end to keep resource handling clean.

## How to test

1. Save the snippet as `solution.php` in this folder.
2. Run:
   ```bash
   php solution.php
   ```
3. Confirm the output is exactly `curl`.
4. If cURL extension is missing, enable it in your local PHP setup and re-run.

← [Zero to PHP](../README.md)
