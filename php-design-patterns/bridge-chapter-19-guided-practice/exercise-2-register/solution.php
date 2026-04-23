<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — Bridge needs two axes; this is one.
 *
 * `CashRegister` and `CardRegister` differ in *how* they take payment.
 * That's a single axis: payment method. Bridge pays its rent when you
 * have *two independent dimensions of variation* (e.g. notification
 * type AND delivery channel). With one axis, plain interface +
 * implementations is correct — that is just polymorphism.
 *
 * Forcing Bridge here would require inventing a second axis (cashier?
 * receipt format?) that does not exist in the brief. That's design
 * by analogy, not by need.
 */

interface Register
{
    public function ringUp(int $totalInPence): string;
}

final class CashRegister implements Register
{
    public function ringUp(int $totalInPence): string { return "cash:{$totalInPence}"; }
}

final class CardRegister implements Register
{
    public function ringUp(int $totalInPence): string { return "card:{$totalInPence}"; }
}

pdp_assert_eq('cash:1500', (new CashRegister())->ringUp(1500), 'cash works');
pdp_assert_eq('card:2500', (new CardRegister())->ringUp(2500), 'card works');

pdp_done('Bridge was the wrong answer here — one axis is plain polymorphism. See the comment block.');
