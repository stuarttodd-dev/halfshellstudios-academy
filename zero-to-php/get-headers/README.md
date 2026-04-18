# get_headers

Basic solution for `get-headers` using simulated header lines.

```php
<?php
declare(strict_types=1);

$lines = [
    'HTTP/1.1 302 Found',
    'Content-Type: application/json',
    'Location: /login',
];

echo str_contains($lines[0], '302') && str_contains($lines[2], '/login') ? 'headers' : 'no';
```

Expected output: `headers`.

## Solution walkthrough

This simulates the array shape returned by `get_headers()`, including a status line and redirect header.  
The check confirms the status includes `302` and the redirect location includes `/login`.

## How to test

1. Save the snippet as `solution.php` in this folder.
2. Run:
   ```bash
   php solution.php
   ```
3. Confirm the output is exactly `headers`.

← [Zero to PHP](../README.md)
