# Chapter 7 practice — calculator service

Sample for **Namespaces, autoloading, and Composer** (Academy *Zero to PHP*): a tiny **`App\CalculatorService`** in `src/`, wired through **`vendor/autoload.php`** from **`public/index.php`** — the same layout as a small production app (logic in `src/`, thin entry script).

## Layout

- `composer.json` — PSR-4: `App\` → `src/`
- `src/CalculatorService.php` — `add`, `subtract`, `multiply`, `divide` (divide throws `\InvalidArgumentException` when dividing by zero)
- `public/index.php` — requires Composer autoload, runs the four required calculations in order, then optionally demonstrates the divide-by-zero guard

## Run

From this directory:

```bash
composer install
php public/index.php
```

You should see four numeric lines (`13`, `7`, `20`, `5`) and a fifth line starting with `error:` from the caught exception.

If PHP cannot find the class after adding files, run `composer dump-autoload`.

← [Zero to PHP](../README.md)
