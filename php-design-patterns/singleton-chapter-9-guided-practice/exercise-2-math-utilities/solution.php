<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: Math::clampToRange is fine as a static method.
 *
 * The reason we replace classic Singletons is that they:
 *
 *   - hide a dependency at the call site;
 *   - hold MUTABLE STATE that tests cannot reset;
 *   - couple consumers to a global accessor.
 *
 * `Math::clampToRange(int $n, int $min, int $max): int` is **pure**:
 *   - no state;
 *   - no I/O;
 *   - no clock, no random, no global config;
 *   - referentially transparent — the same inputs always produce the
 *     same output.
 *
 * There is nothing to inject. Instantiating it would mean every caller
 * wires up a `Math` instance to call a single deterministic function.
 *
 * Stateless, side-effect-free helpers are exactly what static methods
 * are for. Don't replace what isn't broken.
 *
 * The only refactor we'd consider: if the project has many tiny
 * helpers like this, group them into a namespace (`App\\Math`) so
 * autoloading remains tidy. The fact that it's static is not the
 * problem.
 */

final class Math
{
    public static function clampToRange(int $n, int $min, int $max): int
    {
        return max($min, min($max, $n));
    }
}

// ---- assertions -------------------------------------------------------------

pdp_assert_eq(5,  Math::clampToRange(5,  0, 10), 'inside range -> unchanged');
pdp_assert_eq(0,  Math::clampToRange(-1, 0, 10), 'below min -> min');
pdp_assert_eq(10, Math::clampToRange(99, 0, 10), 'above max -> max');
pdp_assert_eq(7,  Math::clampToRange(7,  7,  7), 'degenerate range');

// And to make the determinism point: pure functions can be called from
// anywhere, in any order, with no setup, and the answer never changes.
for ($i = 0; $i < 3; $i++) {
    pdp_assert_eq(5, Math::clampToRange(5, 0, 10), "pure: same inputs -> same output (run #{$i})");
}

pdp_done('(Singleton-replacement was the wrong answer — see the comment block.)');
