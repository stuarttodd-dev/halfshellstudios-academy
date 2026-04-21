<?php
declare(strict_types=1);

final class Subscription
{
    public function __construct(
        public readonly int                $id,
        public readonly string             $status,
        public readonly DateTimeImmutable  $renewsAt,
        public readonly ?DateTimeImmutable $cancelledAt,
    ) {}
}

/**
 * The rule "is this subscription eligible for an upgrade?" lives in the
 * caller. Three predicates AND'd together, all reaching into the
 * subscription's fields. Two callers exist (controller + cron), so the
 * predicate is duplicated, and `new DateTimeImmutable('+7 days')` makes
 * the result depend on whatever clock the caller happens to use.
 */
function offerUpgradeIfEligible(Subscription $subscription, array &$offers): void
{
    if ($subscription->status === 'active' &&
        $subscription->renewsAt > new DateTimeImmutable('+7 days') &&
        $subscription->cancelledAt === null) {
        $offers[] = $subscription->id;
    }
}

/* ---------- driver ---------- */

$now    = new DateTimeImmutable('2026-04-20T12:00:00');
$inDays = fn (int $d) => $now->modify("+{$d} days");

$subscriptions = [
    new Subscription(id: 1, status: 'active',    renewsAt: $inDays(30), cancelledAt: null),         // eligible
    new Subscription(id: 2, status: 'active',    renewsAt: $inDays(3),  cancelledAt: null),         // too close to renewal
    new Subscription(id: 3, status: 'paused',    renewsAt: $inDays(30), cancelledAt: null),         // wrong status
    new Subscription(id: 4, status: 'active',    renewsAt: $inDays(30), cancelledAt: $inDays(-1)),  // cancelled
    new Subscription(id: 5, status: 'active',    renewsAt: $inDays(8),  cancelledAt: null),         // just-eligible (>7d)
];

$offers = [];
foreach ($subscriptions as $subscription) {
    offerUpgradeIfEligible($subscription, $offers);
}

echo "starter (wall clock):  offers = " . json_encode($offers) . "\n";
echo "                       NOTE: this answer depends on what day you run the script\n";
echo "                             because the predicate uses 'new DateTimeImmutable(\"+7 days\")'\n";
