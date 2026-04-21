<?php
declare(strict_types=1);

/**
 * Where to put the interface — and why "right" matters.
 *
 * There are at least three plausible boundaries for this dependency:
 *
 *   (a) `HttpClient` — "the thing that lets us GET a URL".
 *   (b) `JsonHttpClient` — "the thing that GETs a URL and decodes JSON".
 *   (c) `ExchangeRateProvider` — "the thing that gives us a rate
 *        between two currencies".
 *
 * (a) and (b) are too low-level. They leak the *transport* into the
 * domain. Tests for `ConvertCurrency` would then have to know about
 * URLs, JSON shape, and the API's quirks. Worse, every other consumer
 * that needs a rate also has to know how the rate is fetched.
 *
 * (c) names the question we actually want to ask. The HTTP call, the
 * URL template, the JSON shape, the API key — all of that lives in
 * **one** adapter (`HttpExchangeRateProvider`) that nobody else has to
 * know about. Tests use `InMemoryExchangeRateProvider` and never touch
 * a network primitive.
 *
 * Rule of thumb: an interface lives at the level of the *question
 * being asked*, not the *primitive being used*.
 */

interface ExchangeRateProvider
{
    public function rateFor(string $from, string $to): float;
}

final class ConvertCurrency
{
    public function __construct(private ExchangeRateProvider $rates) {}

    public function convert(int $amountInPence, string $from, string $to): int
    {
        return (int) round($amountInPence * $this->rates->rateFor($from, $to));
    }
}

/* ---------- production adapter ---------- */

final class HttpExchangeRateProvider implements ExchangeRateProvider
{
    public function __construct(private string $baseUrl = 'https://api.example.com/rate') {}

    public function rateFor(string $from, string $to): float
    {
        $url      = "{$this->baseUrl}/{$from}/{$to}";
        $response = file_get_contents($url);

        if ($response === false) {
            throw new RuntimeException("Could not fetch rate {$from}->{$to} from {$url}");
        }

        $decoded = json_decode($response);
        if (! is_object($decoded) || ! isset($decoded->rate)) {
            throw new RuntimeException("Malformed rate response from {$url}");
        }

        return (float) $decoded->rate;
    }
}

/* ---------- test double ---------- */

final class InMemoryExchangeRateProvider implements ExchangeRateProvider
{
    /** @param array<string, array<string, float>> $rates */
    public function __construct(private array $rates) {}

    public function rateFor(string $from, string $to): float
    {
        return $this->rates[$from][$to]
            ?? throw new RuntimeException("No in-memory rate for {$from}->{$to}");
    }
}

/* ---------- millisecond-fast test — no network ---------- */

$rates = new InMemoryExchangeRateProvider([
    'GBP' => ['EUR' => 1.17, 'USD' => 1.25],
    'EUR' => ['GBP' => 0.85],
]);

$converter = new ConvertCurrency($rates);

assert($converter->convert(amountInPence: 1_000, from: 'GBP', to: 'EUR') === 1170);
assert($converter->convert(amountInPence: 1_000, from: 'GBP', to: 'USD') === 1250);
assert($converter->convert(amountInPence:   500, from: 'EUR', to: 'GBP') === 425);

echo "GBP 1000p -> EUR " . $converter->convert(1_000, 'GBP', 'EUR') . "p\n";
echo "GBP 1000p -> USD " . $converter->convert(1_000, 'GBP', 'USD') . "p\n";
echo "EUR  500p -> GBP " . $converter->convert(  500, 'EUR', 'GBP') . "p\n";
echo "(notice: ran in milliseconds, hit no network)\n";
