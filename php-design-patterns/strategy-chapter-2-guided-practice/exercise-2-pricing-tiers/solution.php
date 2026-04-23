<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: Strategy is the WRONG answer here. Keep the `match`.
 *
 * Each "branch" is a single integer with no behaviour attached. There
 * is nothing to vary — no rule, no policy, no algorithm. A
 * `FreePricing` / `BasicPricing` / `ProPricing` / `TeamPricing` class
 * that returns one constant each is ceremony around four numbers.
 *
 * The right home for "data that varies by enum-like key" is a small
 * data table — exactly what the starter's `match` already is.
 *
 * If pricing GAINS behaviour (per-tier limits, trial windows, currency
 * conversions, …), that is the moment to extract Strategy. We have not
 * earned it yet.
 *
 * What we DO want to fix in the starter:
 *   - the implicit string parameter (`$tier`) becomes a `Tier` enum so
 *     the typo `'beam'` is a compile-time error, not a runtime one;
 *   - prices are pence-typed so the units are obvious in the signature.
 *
 * That is one refactor — and it is *not* Strategy.
 */

enum Tier: string
{
    case Free  = 'free';
    case Basic = 'basic';
    case Pro   = 'pro';
    case Team  = 'team';
}

final class TierPricing
{
    public function priceInPence(Tier $tier): int
    {
        return match ($tier) {
            Tier::Free  => 0,
            Tier::Basic => 999,
            Tier::Pro   => 2999,
            Tier::Team  => 9999,
        };
    }
}

// ---- assertions -------------------------------------------------------------

$pricing = new TierPricing();
pdp_assert_eq(0,    $pricing->priceInPence(Tier::Free),  'Free = 0p');
pdp_assert_eq(999,  $pricing->priceInPence(Tier::Basic), 'Basic = 999p');
pdp_assert_eq(2999, $pricing->priceInPence(Tier::Pro),   'Pro = 2999p');
pdp_assert_eq(9999, $pricing->priceInPence(Tier::Team),  'Team = 9999p');

// Typos are now ValueError at the boundary, not an unhandled match arm.
pdp_assert_throws(\ValueError::class, fn () => Tier::from('beam'), "typoed 'beam' rejected by Tier::from()");

pdp_done('(Strategy was the wrong answer — see the comment block.)');
