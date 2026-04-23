<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — these two classes don't share a workflow.
 *
 * Template Method earns its keep when several subclasses share a real
 * multi-step workflow (e.g. "fetch -> transform -> format -> audit") and
 * differ only in one or two steps. `GreetingService::greet` and
 * `FarewellService::farewell` are *one-line methods* that just happen
 * to take a string and return a string.
 *
 * Forcing them into a base class would mean inventing a workflow that
 * does not exist, naming an "abstract phrase()" hook, and making readers
 * follow inheritance to learn what is essentially `"Hello, $name!"`.
 *
 * Two trivial functions/methods with no shared shape — leave them.
 */

final class GreetingService
{
    public function greet(string $name): string { return "Hello, {$name}!"; }
}

final class FarewellService
{
    public function farewell(string $name): string { return "Goodbye, {$name}!"; }
}

pdp_assert_eq('Hello, Sam!',   (new GreetingService())->greet('Sam'),   'greeting works as-is');
pdp_assert_eq('Goodbye, Sam!', (new FarewellService())->farewell('Sam'), 'farewell works as-is');

pdp_done('Template Method was the wrong answer here — see the comment block.');
