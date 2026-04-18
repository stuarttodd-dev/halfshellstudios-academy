# stream_context_create for HTTP

Basic local solution for `stream-context-create-for-http`.

```php
<?php
declare(strict_types=1);

$p = __DIR__ . '/j.txt';
file_put_contents($p, '{"z":2}');

$ctx = stream_context_create(['file' => ['mode' => 'r']]);
$raw = file_get_contents($p, false, $ctx);
unlink($p);

$d = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
echo $d['z'];
```

Expected output: `2`.

## Solution walkthrough

The script writes a small JSON string to a local file, creates a stream context, and reads the file using `file_get_contents(..., false, $ctx)`.  
It then decodes JSON with `JSON_THROW_ON_ERROR` and echoes the `z` value.

## How to test

1. Save the snippet as `solution.php` in this folder.
2. Run:
   ```bash
   php solution.php
   ```
3. Confirm the output is exactly `2`.
4. Re-run once more to confirm the temp file handling still works.

← [Zero to PHP](../README.md)
