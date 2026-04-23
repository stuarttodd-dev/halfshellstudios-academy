<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class SalesReport
{
    /**
     * @param list<string> $regions
     * @param list<string> $productCategories
     */
    public function __construct(
        public readonly \DateTimeImmutable $from,
        public readonly \DateTimeImmutable $to,
        public readonly array $regions,
        public readonly array $productCategories,
        public readonly bool $includeRefunds,
        public readonly string $groupBy,
        public readonly bool $includeUnshipped,
        public readonly ?int $minimumOrderValueInPence,
    ) {}

    public static function builder(): SalesReportBuilder
    {
        return new SalesReportBuilder();
    }
}

final class SalesReportBuilder
{
    private ?\DateTimeImmutable $from = null;
    private ?\DateTimeImmutable $to = null;
    /** @var list<string> */
    private array $regions = [];
    /** @var list<string> */
    private array $productCategories = [];
    private bool $includeRefunds = true;
    private string $groupBy = 'day';
    private bool $includeUnshipped = false;
    private ?int $minimumOrderValueInPence = null;

    public function between(\DateTimeImmutable $from, \DateTimeImmutable $to): self
    {
        $this->from = $from; $this->to = $to;
        return $this;
    }

    /** @param list<string> $regions */
    public function forRegions(array $regions): self { $this->regions = array_values($regions); return $this; }

    /** @param list<string> $categories */
    public function forCategories(array $categories): self { $this->productCategories = array_values($categories); return $this; }

    public function groupedBy(string $unit): self { $this->groupBy = $unit; return $this; }

    public function excludingRefunds(): self  { $this->includeRefunds = false; return $this; }
    public function includingUnshipped(): self { $this->includeUnshipped = true; return $this; }

    public function minimumOrderValue(int $pence): self
    {
        if ($pence < 0) throw new \InvalidArgumentException('minimumOrderValue must be >= 0');
        $this->minimumOrderValueInPence = $pence;
        return $this;
    }

    public function build(): SalesReport
    {
        if ($this->from === null || $this->to === null) {
            throw new \LogicException('between(from, to) is required');
        }
        if ($this->from >= $this->to) {
            throw new \LogicException('from must be < to');
        }
        if (!in_array($this->groupBy, ['day', 'week', 'month'], true)) {
            throw new \LogicException("groupBy must be one of day|week|month, got '{$this->groupBy}'");
        }

        return new SalesReport(
            from: $this->from,
            to: $this->to,
            regions: $this->regions,
            productCategories: $this->productCategories,
            includeRefunds: $this->includeRefunds,
            groupBy: $this->groupBy,
            includeUnshipped: $this->includeUnshipped,
            minimumOrderValueInPence: $this->minimumOrderValueInPence,
        );
    }
}

// ---- assertions -------------------------------------------------------------

$report = SalesReport::builder()
    ->between(new \DateTimeImmutable('2026-04-01'), new \DateTimeImmutable('2026-05-01'))
    ->forRegions(['UK', 'EU'])
    ->groupedBy('week')
    ->excludingRefunds()
    ->minimumOrderValue(1000)
    ->build();

pdp_assert_eq('2026-04-01', $report->from->format('Y-m-d'), 'from');
pdp_assert_eq('2026-05-01', $report->to->format('Y-m-d'),   'to');
pdp_assert_eq(['UK', 'EU'], $report->regions, 'regions');
pdp_assert_eq('week',       $report->groupBy, 'groupBy');
pdp_assert_eq(false,        $report->includeRefunds, 'refunds excluded');
pdp_assert_eq(1000,         $report->minimumOrderValueInPence, 'minimum order value');

pdp_assert_throws(\LogicException::class, fn () => SalesReport::builder()->build(), 'missing range raises');
pdp_assert_throws(
    \LogicException::class,
    fn () => SalesReport::builder()
        ->between(new \DateTimeImmutable('2026-05-01'), new \DateTimeImmutable('2026-04-01'))
        ->build(),
    'inverted range raises',
);
pdp_assert_throws(
    \LogicException::class,
    fn () => SalesReport::builder()
        ->between(new \DateTimeImmutable('2026-04-01'), new \DateTimeImmutable('2026-05-01'))
        ->groupedBy('hour')
        ->build(),
    'invalid groupBy raises',
);

pdp_done();
