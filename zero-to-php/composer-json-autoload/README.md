# Composer `composer.json` autoload

Sample code for the same Academy chapter: **`composer.json` autoload** maps namespaces to directories; **`vendor/autoload.php`** is the generated bootstrap you `require` once so PSR-4 classes load without manual `require` per file.

- `composer.json` — `autoload.psr-4` maps `App\` to `src/`
- `src/` — namespaced classes under that prefix
- `public/index.php` — includes `vendor/autoload.php` (created when you run Composer in this directory)

← [Zero to PHP](../README.md)
