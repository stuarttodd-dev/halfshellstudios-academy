<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: Command is the WRONG answer here.
 *
 * The starter is a QUERY — "give me the customer with id N". A
 * Command represents an INTENT TO CHANGE STATE: register, cancel,
 * charge, ship.
 *
 * Rules of thumb (CQRS-friendly):
 *
 *   - **Commands** alter state, return little (an id, void), and are
 *     things you can audit, queue, replay, and authorise.
 *   - **Queries** read state, return data, are pure (within the
 *     transaction) and benefit from CACHING, not from middleware-style
 *     bookkeeping.
 *
 * Wrapping a query in `GetCustomerCommand` + `GetCustomerHandler`
 * gains nothing and loses things:
 *
 *   - the return type becomes `mixed` (because the bus is generic);
 *   - calling code reads worse: `$bus->dispatch(new GetCustomerCommand($id))`
 *     vs `$customers->find($id)`;
 *   - audit / transaction middleware now wraps every read, including
 *     ones that should not be audited at all.
 *
 * The right shape: keep the read as a method call on a repository or
 * a small query service. If it grows, make it a `CustomerQuery`
 * dedicated class — not a Command.
 */

interface CustomerRepository { public function find(int $id): ?object; }
final class InMemoryCustomerRepository implements CustomerRepository
{
    /** @param array<int, object> $byId */
    public function __construct(private array $byId = []) {}
    public function add(object $customer): void { $this->byId[$customer->id] = $customer; }
    public function find(int $id): ?object { return $this->byId[$id] ?? null; }
}

final class CustomerController
{
    public function __construct(private readonly CustomerRepository $customers) {}
    public function show(int $id): ?object
    {
        return $this->customers->find($id);
    }
}

// ---- assertions -------------------------------------------------------------

$repo = new InMemoryCustomerRepository();
$repo->add((object) ['id' => 1, 'name' => 'Alice']);
$repo->add((object) ['id' => 2, 'name' => 'Bob']);

$controller = new CustomerController($repo);

pdp_assert_eq('Alice', $controller->show(1)?->name, 'returns the customer');
pdp_assert_eq('Bob',   $controller->show(2)?->name, 'returns the other customer');
pdp_assert_eq(null,    $controller->show(99),       'unknown id -> null (no exception, no command bus)');

pdp_done('(Command was the wrong answer for a query — see the comment block.)');
