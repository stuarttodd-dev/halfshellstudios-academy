# Composer `composer.json` autoload (hands-on)

Minimal example of **PSR-4 autoloading** with Composer:

- `composer.json` — `autoload.psr-4` maps `App\` to `src/`
- `src/` — namespaced PHP classes
- `public/index.php` — loads `vendor/autoload.php` and runs the demo

## Run it

1. `composer install`
2. `php public/index.php`

← [Zero to PHP](../README.md)
