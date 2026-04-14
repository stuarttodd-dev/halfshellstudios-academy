# Zero to PHP

This folder in **this repository** holds sample PHP that goes with the Academy track on **namespaces, autoloading, and Composer** — including lessons such as `composer-json-autoload`, **[chapter-7-calculator](chapter-7-calculator/)** (Chapter 7 practice: calculator service + entry script), and **`vendor-autoload-php`** (what `vendor/autoload.php` is for).

## What Composer autoload does

In `composer.json`, the `autoload` section (often **PSR-4**) tells Composer how your namespace prefixes map to folders on disk. When you run **`composer install`** or **`composer dump-autoload`**, Composer generates **`vendor/autoload.php`**. Your application includes that single file once; it registers Composer’s autoloader with PHP so classes are loaded automatically when you reference them (for example with `use App\SomeClass`) instead of adding a `require` for every class file by hand.

The **[composer-json-autoload](composer-json-autoload/)** directory is a minimal layout: `App\` → `src/`, and an entry script that `require`s `vendor/autoload.php`.

The **[chapter-7-calculator](chapter-7-calculator/)** directory is the Chapter 7 practice exercise: `App\CalculatorService` plus `public/index.php` driving add, subtract, multiply, and divide with Composer autoload.

← [Half Shell Studios Academy](../README.md)
