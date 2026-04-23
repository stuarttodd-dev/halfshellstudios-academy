<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — `Money` is cheap to construct.
 *
 * Prototype earns its keep when:
 *   - construction is expensive (parses files, hits the network, runs
 *     long initialisation),
 *   - or a fully configured "template" is meaningfully reusable.
 *
 * `new Money($amount, $currency)` is two field assignments. There is
 * nothing to clone away from. `withAmount()` is a perfectly fine
 * ergonomic for immutable value objects, but that's just the
 * `with*` idiom — not the Prototype pattern.
 *
 * Below: an immutable Money with a `withAmount()` helper. No registry,
 * no clone-then-mutate ceremony.
 */

final class Money
{
    public function __construct(
        public readonly int $amountInPence,
        public readonly string $currency = 'GBP',
    ) {}

    public function withAmount(int $newAmount): self
    {
        return new self($newAmount, $this->currency);
    }
}

$five = new Money(500, 'GBP');
$ten  = $five->withAmount(1_000);

pdp_assert_eq(500,  $five->amountInPence, 'original immutable');
pdp_assert_eq(1_000, $ten->amountInPence, 'with* returned a new Money');
pdp_assert_eq('GBP', $ten->currency, 'currency preserved');
pdp_assert_true($five !== $ten, 'distinct instances');

pdp_done('Prototype was the wrong answer here — see the comment block.');
