<?php
declare(strict_types=1);

/**
 * The class hits the network inline. There is no way to test it
 * without either a live HTTP endpoint or a global function override.
 *
 * "Convert £10 from GBP to EUR" should not require a network round
 * trip in CI.
 */
final class ConvertCurrency
{
    public function convert(int $amountInPence, string $from, string $to): int
    {
        $rate = json_decode(file_get_contents("https://api.example.com/rate/{$from}/{$to}"))->rate;

        return (int) round($amountInPence * $rate);
    }
}

/* ---------- "test" — would need either real HTTP or a stream wrapper hack ---------- */

echo "starter cannot run a test offline (would hit https://api.example.com)\n";
