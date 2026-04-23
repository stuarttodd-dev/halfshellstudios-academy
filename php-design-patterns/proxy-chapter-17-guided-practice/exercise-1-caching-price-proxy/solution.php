<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class Money
{
    public function __construct(public readonly int $amountInPence, public readonly string $currency = 'GBP') {}
    public function equals(Money $o): bool { return $this->amountInPence === $o->amountInPence && $this->currency === $o->currency; }
}

interface PriceService
{
    public function priceFor(int $productId): Money;
}

interface Clock { public function now(): int; }

final class FixedClock implements Clock
{
    public function __construct(public int $now) {}
    public function now(): int { return $this->now; }
    public function advance(int $seconds): void { $this->now += $seconds; }
}

/** Real implementation — would call out to a slow API. */
final class ApiPriceService implements PriceService
{
    public int $callCount = 0;
    /** @param array<int,int> $pricesByProduct */
    public function __construct(public array $pricesByProduct = []) {}
    public function priceFor(int $productId): Money
    {
        $this->callCount++;
        return new Money($this->pricesByProduct[$productId] ?? 0);
    }
}

final class CachingPriceService implements PriceService
{
    /** @var array<int, array{value: Money, expiresAt: int}> */
    private array $cache = [];

    public function __construct(
        private readonly PriceService $inner,
        private readonly Clock $clock,
        private readonly int $ttlSeconds = 60,
    ) {}

    public function priceFor(int $productId): Money
    {
        $now = $this->clock->now();
        if (isset($this->cache[$productId]) && $this->cache[$productId]['expiresAt'] > $now) {
            return $this->cache[$productId]['value'];
        }
        $value = $this->inner->priceFor($productId);
        $this->cache[$productId] = ['value' => $value, 'expiresAt' => $now + $this->ttlSeconds];
        return $value;
    }
}

// ---- assertions -------------------------------------------------------------

$api = new ApiPriceService([1 => 1_000, 2 => 2_500]);
$clock = new FixedClock(now: 1_000);
$cached = new CachingPriceService($api, $clock, ttlSeconds: 60);

pdp_assert_eq(1_000, $cached->priceFor(1)->amountInPence, 'first call returns API value');
pdp_assert_eq(1, $api->callCount, 'first call hit the API once');

$cached->priceFor(1);
$cached->priceFor(1);
pdp_assert_eq(1, $api->callCount, 'subsequent calls served from cache');

pdp_assert_eq(2_500, $cached->priceFor(2)->amountInPence, 'different id misses cache');
pdp_assert_eq(2, $api->callCount, 'API hit again for new id');

$clock->advance(61);
pdp_assert_eq(1_000, $cached->priceFor(1)->amountInPence, 'after TTL expires, refetch');
pdp_assert_eq(3, $api->callCount, 'API hit a third time after TTL');

// proxy is interchangeable with the real impl
function priceFromAnyService(PriceService $s, int $id): int { return $s->priceFor($id)->amountInPence; }
pdp_assert_eq(2_500, priceFromAnyService($cached, 2), 'caller depends only on the interface');

pdp_done();
