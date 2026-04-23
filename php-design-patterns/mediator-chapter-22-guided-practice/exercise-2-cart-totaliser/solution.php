<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — two collaborating objects don't need a hub.
 *
 * Mediator earns its keep when N components would otherwise hold
 * direct references to N - 1 others, leaving you with O(N²) coupling.
 * Two objects (Cart + CartTotaliser) is N = 2: one collaborator each.
 * Inserting a `CartMediator` would add an empty class and one more
 * indirection without removing any coupling.
 *
 * Direct collaboration is the right shape here.
 */

final class CartTotaliser
{
    /** @param list<array{qty:int, unitPriceInPence:int}> $lines */
    public function total(array $lines): int
    {
        $sum = 0;
        foreach ($lines as $l) $sum += $l['qty'] * $l['unitPriceInPence'];
        return $sum;
    }
}

final class Cart
{
    /** @var list<array{qty:int, unitPriceInPence:int}> */
    public array $lines = [];

    public function __construct(private readonly CartTotaliser $totaliser) {}

    public function add(int $qty, int $unitPriceInPence): void
    {
        $this->lines[] = ['qty' => $qty, 'unitPriceInPence' => $unitPriceInPence];
    }

    public function total(): int { return $this->totaliser->total($this->lines); }
}

// ---- assertions -------------------------------------------------------------

$cart = new Cart(new CartTotaliser());
$cart->add(2, 1_000);
$cart->add(1, 500);

pdp_assert_eq(2_500, $cart->total(), 'direct collaboration is fine');

pdp_done('Mediator was the wrong answer here — see the comment block.');
