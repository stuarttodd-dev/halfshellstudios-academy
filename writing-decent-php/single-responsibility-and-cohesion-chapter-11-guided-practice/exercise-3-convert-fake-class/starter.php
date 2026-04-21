<?php
declare(strict_types=1);

/**
 * `StringHelpers` is a "fake class": a bag of static methods, no state,
 * no invariants. Calling `StringHelpers::slugify($title)` is identical
 * to calling a function — the class adds nothing except a typing-cost
 * tax on every caller.
 */
final class StringHelpers
{
    public static function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
        return trim($s, '-');
    }

    /** @param list<string> $taken */
    public static function ensureUnique(string $base, array $taken): string
    {
        if (! in_array($base, $taken, true)) {
            return $base;
        }

        $i = 2;
        while (in_array("{$base}-{$i}", $taken, true)) {
            $i++;
        }
        return "{$base}-{$i}";
    }
}

/* ---------- driver ---------- */

$titles = ['Hello, World!', 'PHP 8.2 — value objects', 'Hello, World!'];
$taken  = ['hello-world'];

$slugs = [];
foreach ($titles as $title) {
    $base   = StringHelpers::slugify($title);
    $unique = StringHelpers::ensureUnique($base, $taken);
    $taken[] = $unique;
    $slugs[] = $unique;
}

echo "slugs: " . json_encode($slugs) . "\n";
