<?php
declare(strict_types=1);

/**
 * Tiny assertion helpers shared by every exercise's `solution.php`.
 *
 * No PHPUnit, no autoloader, no composer install needed: every
 * solution is a single file you can run with `php solution.php`.
 */

if (!function_exists('pdp_assert_eq')) {
    function pdp_assert_eq(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            echo "FAIL: {$message}\n  expected: " . var_export($expected, true) . "\n  actual:   " . var_export($actual, true) . "\n";
            exit(1);
        }
        echo "PASS: {$message}\n";
    }

    function pdp_assert_true(bool $cond, string $message): void
    {
        if (!$cond) {
            echo "FAIL: {$message}\n";
            exit(1);
        }
        echo "PASS: {$message}\n";
    }

    function pdp_assert_throws(string $exceptionClass, callable $fn, string $message): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            if ($e instanceof $exceptionClass) {
                echo "PASS: {$message}\n";
                return;
            }
            echo "FAIL: {$message} (wrong exception: " . $e::class . " — '{$e->getMessage()}')\n";
            exit(1);
        }
        echo "FAIL: {$message} (no exception thrown)\n";
        exit(1);
    }

    function pdp_done(string $note = ''): void
    {
        echo "\nAll assertions passed." . ($note !== '' ? "  {$note}" : '') . "\n";
    }
}
