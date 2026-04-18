# Chapter 7 recap — calculator solution (step by step)

This recap walks through the **Chapter 7 practice** solution: a small **PSR-4** project with `App\CalculatorService` in `src/` and a thin **`public/index.php`** entry script.

**GitHub — sample project (canonical):** <https://github.com/stuarttodd-dev/halfshellstudios-academy/tree/main/zero-to-php/chapter-7-calculator>

Clone or browse that folder and follow along. Per-file links below point into the same tree.

---

## 1. Why this shape?

Real PHP apps usually:

- Put **domain logic** in namespaced classes under `src/`.
- Keep a **single public entry** (here `public/index.php`) that only boots Composer and calls your code.

Composer’s **autoload** maps `App\` → `src/` so you never hand-`require` every class file.

---

## 2. Map the namespace with `composer.json`

Open [`composer.json`](https://github.com/stuarttodd-dev/halfshellstudios-academy/blob/main/zero-to-php/chapter-7-calculator/composer.json). The important part is **`autoload.psr-4`**:

- Namespace prefix **`App\\`** (note the escaped backslash in JSON).
- Directory **`src/`** — so `App\CalculatorService` must live at `src/CalculatorService.php`.

Run **`composer install`** in `chapter-7-calculator/` so Composer creates **`vendor/`** and **`vendor/autoload.php`**. That file registers the autoloader; your script includes it once.

---

## 3. Which file does what?

| File | Responsibility |
|------|------------------|
| **`src/CalculatorService.php`** | The **service**: arithmetic only. No `echo`, no `require`. Namespaced class `App\CalculatorService`. |
| **`public/index.php`** | The **entry script**: loads Composer’s autoloader, creates the service, **prints** results (and catches the optional divide-by-zero error). |

Everything below breaks those two files down in detail.

---

## 4. File breakdown: `src/CalculatorService.php`

Source: [`src/CalculatorService.php`](https://github.com/stuarttodd-dev/halfshellstudios-academy/blob/main/zero-to-php/chapter-7-calculator/src/CalculatorService.php)

```php
<?php

declare(strict_types=1);

namespace App;

final class CalculatorService
{
    public function add(float $a, float $b): float
    {
        return $a + $b;
    }

    public function subtract(float $a, float $b): float
    {
        return $a - $b;
    }

    public function multiply(float $a, float $b): float
    {
        return $a * $b;
    }

    public function divide(float $a, float $b): float
    {
        if ($b === 0.0) {
            throw new \InvalidArgumentException('Cannot divide by zero.');
        }

        return $a / $b;
    }
}
```

**Line by line (blocks):**

1. **`<?php`** — Opens the PHP block (no closing `?>`; that avoids stray whitespace after the file).

2. **`declare(strict_types=1);`** — Turns on **strict typing** for this file. Scalar arguments are checked against parameter types (`float`, etc.) without silent coercion, which keeps math predictable.

3. **`namespace App;`** — Puts this class in the **`App`** namespace. With PSR-4, the full class name is **`App\CalculatorService`**, matching the folder `src/` mapped in `composer.json`.

4. **`final class CalculatorService`** — **`final`** means nothing subclasses this class in the exercise; the API is fixed. The class name **`CalculatorService`** matches the filename **`CalculatorService.php`** (PSR-4).

5. **`add` / `subtract` / `multiply`** — Each takes two **`float`** operands and returns a **`float`**. They only **return** a value; they do not print or read input.

6. **`divide`** — Same signature, but:
   - If the divisor **`$b`** is **`0.0`**, the method **must not** return a bogus number. It throws **`new \InvalidArgumentException('...')`**.
   - The leading **`\`** means **`InvalidArgumentException`** is resolved from the **global** namespace (PHP’s built-in exception class), not `App\InvalidArgumentException`.
   - Otherwise it returns **`$a / $b`**.

**Design rule:** the service stays **pure** — only calculations and valid throws. Any **printing** belongs in the entry script.

---

## 5. File breakdown: `public/index.php`

Source: [`public/index.php`](https://github.com/stuarttodd-dev/halfshellstudios-academy/blob/main/zero-to-php/chapter-7-calculator/public/index.php)

```php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\CalculatorService;

$calculator = new CalculatorService();

echo $calculator->add(10, 3) . PHP_EOL;
echo $calculator->subtract(10, 3) . PHP_EOL;
echo $calculator->multiply(4, 5) . PHP_EOL;
echo $calculator->divide(20, 4) . PHP_EOL;

try {
    $calculator->divide(1, 0);
} catch (\InvalidArgumentException $e) {
    echo 'error: ' . $e->getMessage() . PHP_EOL;
}
```

**Line by line (blocks):**

1. **`<?php` and `declare(strict_types=1);`** — Same idea as the service file: this script also runs under strict types.

2. **`require dirname(__DIR__) . '/vendor/autoload.php';`**
   - This file lives in **`public/index.php`**, so **`__DIR__`** is the `public/` folder.
   - **`dirname(__DIR__)`** is the **project root** (one level above `public/`).
   - **`/vendor/autoload.php`** is the file Composer generates. Including it **once** registers the autoloader so **`App\...`** classes load when used.

3. **`use App\CalculatorService;`** — Imports the short name **`CalculatorService`** so you can write **`new CalculatorService()`** instead of **`new \App\CalculatorService()`**. (You could skip `use` and instantiate with the fully qualified name instead.)

4. **`$calculator = new CalculatorService();`** — Creates the service. No `echo` yet; this is **composition**: the script owns the object and will call methods on it.

5. **Four `echo` lines (the exercise self-check)** — Calls **`add` → `subtract` → `multiply` → `divide`** with the required operands, **in that order**, and prints each result on its own line:
   - **`add(10, 3)`** → first line **13**
   - **`subtract(10, 3)`** → **7**
   - **`multiply(4, 5)`** → **20**
   - **`divide(20, 4)`** → **5**  
   **`PHP_EOL`** is the correct newline for the current OS (CLI-friendly).

6. **`try` / `catch` (optional but in the sample)** — Calls **`divide(1, 0)`**, which triggers **`InvalidArgumentException`** from the service. The **`catch (\InvalidArgumentException $e)`** uses a leading **`\`** again for the global exception class. **`$e->getMessage()`** surfaces the message (e.g. for an **`error:`** line in the output).

**Why keep `public/` thin?** If you later add HTTP, tests, or another entry point, they can all **`require` the same autoloader** and reuse **`CalculatorService`** without duplicating math.

---

## 6. Run it

From the `chapter-7-calculator` directory:

```bash
php public/index.php
```

Expected (numbers may appear as `13` or `13.0` depending on how PHP stringifies floats):

```text
13
7
20
5
error: Cannot divide by zero.
```

(Last line matches the sample message in the repo.)

---

## 7. If PHP says “class not found”

From the project root:

```bash
composer dump-autoload
```

Then run `php public/index.php` again. That refreshes Composer’s map after you add or rename classes under `src/`.

---

## 8. What you practised

- **PSR-4** file layout: class name ↔ file path under `src/`.
- **Composer autoload**: one `require` for `vendor/autoload.php`.
- **Separation**: calculator logic in **`CalculatorService`**, orchestration and I/O in **`public/index.php`**.
- **Safe API**: **`divide`** rejects zero with a clear exception instead of returning a meaningless value.

← [Zero to PHP](README.md)
