<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — a bool is fine.
 *
 * State pays its rent when each state has *different behaviour* across
 * several operations: an `OpenConnection` and a `ClosedConnection` accept
 * different messages, throw different errors, and react differently to
 * `close()`. A `FeatureToggle::evaluate()` returns the same bool no matter
 * which "state" we are in. There is exactly one operation, and it is a
 * field read.
 *
 * Replacing the bool with `EnabledState` and `DisabledState` would buy
 * you two classes, an interface, a context, and a constructor argument
 * — all to read one bool. Save the pattern for genuine state machines.
 */

final class FeatureToggle
{
    public function __construct(public bool $enabled = false) {}
    public function evaluate(?object $user = null): bool { return $this->enabled; }
}

$t = new FeatureToggle();
pdp_assert_eq(false, $t->evaluate(), 'disabled by default');

$t->enabled = true;
pdp_assert_eq(true, $t->evaluate(), 'flipped on');

pdp_done('State was the wrong answer here — a bool is sufficient.');
