# Phar::mapPhar awareness

Basic solution for `phar-mapphar-awareness`.

```php
<?php
declare(strict_types=1);

$stub = <<<'PHP'
#!/usr/bin/env php
<?php
Phar::mapPhar();
__HALT_COMPILER();
PHP;

echo str_contains($stub, 'mapPhar') && str_contains($stub, '__HALT_COMPILER') ? 'phar' : 'no';
```

Expected output: `phar`.

## Solution walkthrough

This builds a minimal PHAR-style stub as a string, then checks for the two key markers: `Phar::mapPhar()` and `__HALT_COMPILER()`.  
It prints `phar` only when both are present.

## How to test

1. Save the snippet as `solution.php` in this folder.
2. Run:
   ```bash
   php solution.php
   ```
3. Confirm the output is exactly `phar`.

← [Zero to PHP](../README.md)
