<?php
declare(strict_types=1);

/**
 * Cohesion review:
 *
 *   The predicate touches three subscription fields and zero outside
 *   data. The rule belongs to the subscription, not to the caller. The
 *   only outside input is "what counts as 'now?'" — pass that in
 *   explicitly so the rule is a pure question about the entity, not
 *   about the wall clock.
 *
 *   Also: `new DateTimeImmutable('+7 days')` has TWO problems —
 *     (a) it's untestable (the answer changes every time the test runs);
 *     (b) the "7 days" magic number is invisible if you only read the
 *         caller. Hoisting it onto the entity puts the policy and its
 *         constant next to each other.
 */
final class Subscription
{
    private const UPGRADE_NOTICE_PERIOD = '+7 days';

    public function __construct(
        public readonly int                $id,
        public readonly string             $status,
        public readonly DateTimeImmutable  $renewsAt,
        public readonly ?DateTimeImmutable $cancelledAt,
    ) {}

    public function isEligibleForUpgrade(DateTimeImmutable $now): bool
    {
        return $this->isActive()
            && ! $this->isCancelled()
            && $this->renewsAt > $now->modify(self::UPGRADE_NOTICE_PERIOD);
    }

    private function isActive(): bool
    {
        return $this->status === 'active';
    }

    private function isCancelled(): bool
    {
        return $this->cancelledAt !== null;
    }
}

/* ---------- driver (identical observable output to starter.php) ---------- */

$now    = new DateTimeImmutable('2026-04-20T12:00:00');
$inDays = fn (int $d) => $now->modify("+{$d} days");

$subscriptions = [
    new Subscription(id: 1, status: 'active', renewsAt: $inDays(30), cancelledAt: null),
    new Subscription(id: 2, status: 'active', renewsAt: $inDays(3),  cancelledAt: null),
    new Subscription(id: 3, status: 'paused', renewsAt: $inDays(30), cancelledAt: null),
    new Subscription(id: 4, status: 'active', renewsAt: $inDays(30), cancelledAt: $inDays(-1)),
    new Subscription(id: 5, status: 'active', renewsAt: $inDays(8),  cancelledAt: null),
];

$offers = [];
foreach ($subscriptions as $subscription) {
    if ($subscription->isEligibleForUpgrade($now)) {
        $offers[] = $subscription->id;
    }
}

echo "solution (injected clock): offers = " . json_encode($offers) . "\n";
echo "                           expected: [1, 5] — deterministic, the same on every run.\n";
