<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: Decorator is the WRONG answer for validation.
 *
 * Decorator is for *cross-cutting* concerns that wrap a call without
 * caring about its semantics — caching, logging, timing, retries.
 * Validation is not cross-cutting; it is a **precondition** of the
 * operation. It needs to know what an Order means: which fields are
 * required, what an "empty cart" is, what counts as a valid total.
 *
 * Two failure modes if we force validation into a decorator:
 *
 *   1) The "decorator" embeds business rules that belong inside the
 *      Order or the use case (or, better, are enforced at the
 *      boundary). Now business knowledge is scattered across the
 *      wrapping layer and the core, and they can disagree.
 *
 *   2) The decorator turns into a "validation framework" with rule
 *      registration, rule lookup, etc. — a Big Validator God Object
 *      that knows about every operation it might wrap. That is not
 *      Decorator; that is a small bureaucracy.
 *
 * Where validation actually belongs:
 *
 *   - **At the boundary.** The HTTP layer / form request validates
 *     incoming shape and required fields, then constructs a typed
 *     object that is correct by construction (a value object, an enum,
 *     an `OrderRequest`).
 *   - **Inside the domain.** Invariants ("an order has at least one
 *     line") are enforced in the Order / OrderRequest constructor and
 *     can never be wrong.
 *
 * After that, the OrderProcessor receives an already-valid object and
 * cannot be called incorrectly.
 */

interface OrderProcessor
{
    public function process(object $order): void;
}

/** Boundary-side validation that produces a correct-by-construction OrderRequest. */
final class OrderRequest
{
    /** @param list<array{sku:string, qty:int}> $lines */
    public function __construct(
        public readonly int $customerId,
        public readonly array $lines,
    ) {
        if ($customerId <= 0) throw new \InvalidArgumentException('customerId must be > 0');
        if ($lines === [])    throw new \InvalidArgumentException('order must have at least one line');
        foreach ($lines as $line) {
            if (($line['qty'] ?? 0) <= 0) throw new \InvalidArgumentException('quantities must be > 0');
        }
    }
}

final class RealOrderProcessor implements OrderProcessor
{
    /** @var list<object> */
    public array $processed = [];
    public function process(object $order): void
    {
        $this->processed[] = $order;
    }
}

// ---- assertions -------------------------------------------------------------

// Validation succeeded? Construct the typed object. The processor cannot
// see an invalid order because invalid orders never reach this point.
$req = new OrderRequest(customerId: 1, lines: [['sku' => 'A', 'qty' => 2]]);
$processor = new RealOrderProcessor();
$processor->process($req);
pdp_assert_eq(1, count($processor->processed), 'valid order reached the processor');

// Validation failed? The boundary rejects it before any "decorator" runs.
pdp_assert_throws(\InvalidArgumentException::class, fn () => new OrderRequest(0, [['sku' => 'X', 'qty' => 1]]),
    'invalid customerId rejected at construction');
pdp_assert_throws(\InvalidArgumentException::class, fn () => new OrderRequest(1, []),
    'empty order rejected at construction');
pdp_assert_throws(\InvalidArgumentException::class, fn () => new OrderRequest(1, [['sku' => 'X', 'qty' => 0]]),
    'zero-quantity line rejected at construction');

pdp_done('(Decorator was the wrong answer — see the comment block.)');
