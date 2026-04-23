<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Domain-shaped repository, in our vocabulary and units (pence). */
interface SalesReportRepository
{
    public function totalInPence(int $year, int $month): int;
}

/** A stand-in for the legacy database — exposes raw SQL and dollar amounts. */
final class LegacyReportingDb
{
    /** @var list<array{yr:int,mo:int,amount_dollars:float}> */
    private array $rows;
    /** @param list<array{yr:int,mo:int,amount_dollars:float}> $rows */
    public function __construct(array $rows) { $this->rows = $rows; }

    /** Pretends to be a SQL parser; returns a list of associative rows. */
    public function raw_query(string $sql): array
    {
        if (!preg_match('/yr = (\d+) AND mo = (\d+)/', $sql, $m)) {
            return [];
        }
        $year = (int) $m[1]; $month = (int) $m[2];
        $sum = 0.0;
        foreach ($this->rows as $row) {
            if ($row['yr'] === $year && $row['mo'] === $month) {
                $sum += $row['amount_dollars'];
            }
        }
        return [['s' => $sum]];
    }
}

/**
 * Adapter: implements the domain interface, hides the SQL string,
 * translates dollars-as-float into pence-as-int, and isolates the
 * legacy row shape (`['s' => …]`) inside the boundary.
 */
final class LegacySalesReportAdapter implements SalesReportRepository
{
    public function __construct(private readonly LegacyReportingDb $legacy) {}

    public function totalInPence(int $year, int $month): int
    {
        $rows  = $this->legacy->raw_query(
            "SELECT SUM(amount_dollars) AS s FROM old_sales WHERE yr = {$year} AND mo = {$month}"
        );
        $dollars = (float) ($rows[0]['s'] ?? 0);
        return (int) round($dollars * 100);
    }
}

final class MonthlyReport
{
    public function __construct(private readonly SalesReportRepository $repository) {}
    public function totalSalesInPence(int $year, int $month): int
    {
        return $this->repository->totalInPence($year, $month);
    }
}

// ---- assertions -------------------------------------------------------------

$legacy = new LegacyReportingDb([
    ['yr' => 2026, 'mo' => 4, 'amount_dollars' => 12.34],
    ['yr' => 2026, 'mo' => 4, 'amount_dollars' => 7.66],
    ['yr' => 2026, 'mo' => 5, 'amount_dollars' => 50.00],
]);
$adapter = new LegacySalesReportAdapter($legacy);

// The adapter, in isolation, owns ALL the foreign concerns.
pdp_assert_eq(2000, $adapter->totalInPence(2026, 4), 'adapter sums dollars and converts to pence');
pdp_assert_eq(5000, $adapter->totalInPence(2026, 5), 'adapter handles a different month');
pdp_assert_eq(0,    $adapter->totalInPence(1999, 1), 'adapter returns 0 when the legacy DB has no rows');

// The caller depends on the interface, with no SQL or dollars in sight.
$report = new MonthlyReport($adapter);
pdp_assert_eq(2000, $report->totalSalesInPence(2026, 4), 'MonthlyReport delegates to the repository');

// And it is testable with an in-memory implementation — no legacy SDK required.
$inMemory = new class implements SalesReportRepository {
    public function totalInPence(int $year, int $month): int { return $year * 100 + $month; }
};
pdp_assert_eq(202604, (new MonthlyReport($inMemory))->totalSalesInPence(2026, 4), 'caller works against any SalesReportRepository');

pdp_done();
