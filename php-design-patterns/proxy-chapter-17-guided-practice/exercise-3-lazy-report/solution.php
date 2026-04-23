<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface Report
{
    /** @return list<array<string,mixed>> */
    public function rows(): array;
    public function userId(): int;
}

interface ReportRepository
{
    /** @return list<array<string,mixed>> */
    public function fetchAll(int $userId): array;
}

final class CountingRepo implements ReportRepository
{
    public int $fetchCalls = 0;
    /** @param array<int, list<array<string,mixed>>> $byUser */
    public function __construct(public array $byUser = []) {}
    public function fetchAll(int $userId): array
    {
        $this->fetchCalls++;
        return $this->byUser[$userId] ?? [];
    }
}

final class EagerReport implements Report
{
    /** @var list<array<string,mixed>> */
    public readonly array $rows;
    public function __construct(private readonly int $userId, ReportRepository $repo)
    {
        $this->rows = $repo->fetchAll($this->userId);
    }
    public function rows(): array { return $this->rows; }
    public function userId(): int { return $this->userId; }
}

/** Loads on first read, then memoises. Caller never needs to know. */
final class LazyReport implements Report
{
    private ?array $rows = null;

    public function __construct(
        private readonly int $userId,
        private readonly ReportRepository $repo,
    ) {}

    public function rows(): array
    {
        return $this->rows ??= $this->repo->fetchAll($this->userId);
    }

    public function userId(): int { return $this->userId; }
}

// ---- assertions -------------------------------------------------------------

$repo = new CountingRepo([42 => [['id' => 1], ['id' => 2]]]);

// lazy: construction does not fetch
$lazy = new LazyReport(42, $repo);
pdp_assert_eq(0, $repo->fetchCalls, 'construction of LazyReport does NOT touch the repo');

pdp_assert_eq([['id' => 1], ['id' => 2]], $lazy->rows(), 'first rows() call loads');
pdp_assert_eq(1, $repo->fetchCalls, 'first rows() hit the repo once');

$lazy->rows(); $lazy->rows();
pdp_assert_eq(1, $repo->fetchCalls, 'subsequent rows() use the memoised value');

// existing callers (which depend on Report) keep working
function summariseReport(Report $r): string { return "user=" . $r->userId() . " rows=" . count($r->rows()); }
pdp_assert_eq('user=42 rows=2', summariseReport($lazy), 'caller uses Report interface unchanged');

// eager still works for comparison
$eagerRepo = new CountingRepo([7 => [['x' => 1]]]);
$eager = new EagerReport(7, $eagerRepo);
pdp_assert_eq(1, $eagerRepo->fetchCalls, 'eager loads in constructor');
pdp_assert_eq([['x' => 1]], $eager->rows(), 'eager has rows immediately');

pdp_done();
