# move_uploaded_file recap

Basic solution for `move-uploaded-file-recap` (destination building step).

```php
<?php
declare(strict_types=1);

$tmp = '/tmp/php123';
$uploadsDir = __DIR__ . '/uploads';
$destination = $uploadsDir . '/' . basename('photo.jpg');

echo str_ends_with($destination, '/uploads/photo.jpg') ? 'upload' : 'no';
```

Expected output: `upload`.

## Solution walkthrough

This focuses on the destination path step from an upload flow.  
`basename('photo.jpg')` is used when building the final path so only the filename is used, then the script verifies the destination ends with `/uploads/photo.jpg`.

## How to test

1. Save the snippet as `solution.php` in this folder.
2. Run:
   ```bash
   php solution.php
   ```
3. Confirm the output is exactly `upload`.

← [Zero to PHP](../README.md)
