<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * Design note: where do the totals belong?
 *
 *   - The repository's job is **persistence**: "find me the orders".
 *     "Sum their totals" is computation, not persistence.
 *   - However, summing in PHP after fetching a million rows is wrong.
 *     The right place to sum is the database, in one query.
 *
 * We resolve this by giving the repository a method that is
 * SHAPED LIKE THE QUESTION ("the shipped total for a month"), not
 * shaped like the storage. The repository owns "how to ask the DB
 * efficiently". A separate `OrderStatistics` class can compose
 * repository calls when the question grows beyond a single SQL.
 *
 * For this exercise we put `shippedTotalForMonth` on the repository
 * (because it IS one efficient SQL question) and keep the door open
 * for `OrderStatistics` later.
 */

final class CustomerId
{
    public function __construct(public readonly int $value) {}
}

final class Order
{
    public function __construct(
        public readonly int $id,
        public readonly CustomerId $customerId,
        public readonly int $totalInPence,
        public readonly string $status,           // 'placed' | 'shipped' | 'cancelled' …
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}

interface OrderRepository
{
    public function shippedTotalForMonth(int $year, int $month): int;
    public function mostRecentForCustomer(CustomerId $customerId): ?Order;
}

/** Real implementation — kept here for shape, not exercised. */
final class PdoOrderRepository implements OrderRepository
{
    public function __construct(private readonly \PDO $db) {}

    public function shippedTotalForMonth(int $year, int $month): int
    {
        $stmt = $this->db->prepare(
            "SELECT SUM(total_in_pence) AS s FROM orders
             WHERE YEAR(created_at) = ? AND MONTH(created_at) = ? AND status = 'shipped'"
        );
        $stmt->execute([$year, $month]);
        return (int) ($stmt->fetch(\PDO::FETCH_ASSOC)['s'] ?? 0);
    }

    public function mostRecentForCustomer(CustomerId $customerId): ?Order
    {
        $stmt = $this->db->prepare(
            'SELECT id, customer_id, total_in_pence, status, created_at FROM orders
             WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$customerId->value]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;
        return new Order(
            (int) $row['id'],
            new CustomerId((int) $row['customer_id']),
            (int) $row['total_in_pence'],
            (string) $row['status'],
            new \DateTimeImmutable((string) $row['created_at']),
        );
    }
}

/** In-memory implementation — used in tests. Same contract, no SQL. */
final class InMemoryOrderRepository implements OrderRepository
{
    /** @param list<Order> $orders */
    public function __construct(private array $orders = []) {}
    public function add(Order $order): void { $this->orders[] = $order; }

    public function shippedTotalForMonth(int $year, int $month): int
    {
        $sum = 0;
        foreach ($this->orders as $order) {
            if ($order->status !== 'shipped') continue;
            if ((int) $order->createdAt->format('Y') !== $year)  continue;
            if ((int) $order->createdAt->format('n') !== $month) continue;
            $sum += $order->totalInPence;
        }
        return $sum;
    }

    public function mostRecentForCustomer(CustomerId $customerId): ?Order
    {
        $matching = array_filter($this->orders, static fn (Order $o) => $o->customerId->value === $customerId->value);
        if ($matching === []) return null;
        usort($matching, static fn (Order $a, Order $b) => $b->createdAt <=> $a->createdAt);
        return reset($matching) ?: null;
    }
}

/** Caller depends on the repository interface — no SQL. */
final class ReportService
{
    public function __construct(private readonly OrderRepository $orders) {}
    public function totalForMonth(int $year, int $month): int { return $this->orders->shippedTotalForMonth($year, $month); }
    public function lastForCustomer(CustomerId $customerId): ?Order { return $this->orders->mostRecentForCustomer($customerId); }
}

// ---- assertions -------------------------------------------------------------

$repo = new InMemoryOrderRepository();
$repo->add(new Order(1, new CustomerId(1), 1000, 'shipped',   new \DateTimeImmutable('2026-04-01 10:00')));
$repo->add(new Order(2, new CustomerId(1), 5000, 'shipped',   new \DateTimeImmutable('2026-04-15 11:00')));
$repo->add(new Order(3, new CustomerId(1), 3000, 'cancelled', new \DateTimeImmutable('2026-04-20 12:00')));
$repo->add(new Order(4, new CustomerId(2),  500, 'shipped',   new \DateTimeImmutable('2026-05-01 09:00')));

$service = new ReportService($repo);

pdp_assert_eq(6000, $service->totalForMonth(2026, 4), 'shipped total for April excludes cancelled order');
pdp_assert_eq(500,  $service->totalForMonth(2026, 5), 'shipped total for May');
pdp_assert_eq(0,    $service->totalForMonth(1999, 1), 'no orders -> 0');

pdp_assert_eq(3, $service->lastForCustomer(new CustomerId(1))?->id, 'most recent for customer 1 is order #3');
pdp_assert_eq(4, $service->lastForCustomer(new CustomerId(2))?->id, 'most recent for customer 2 is order #4');
pdp_assert_eq(null, $service->lastForCustomer(new CustomerId(999)), 'no orders for customer -> null');

pdp_done();
