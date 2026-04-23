<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface OrderRepository
{
    /** @return list<array{id:int, total:int}> */
    public function ordersFor(int $userId): array;
}
final class InMemoryOrders implements OrderRepository
{
    /** @param array<int, list<array{id:int,total:int}>> $byUser */
    public function __construct(private readonly array $byUser) {}
    public function ordersFor(int $userId): array { return $this->byUser[$userId] ?? []; }
}

final class AuditTrail
{
    /** @var list<string> */
    public array $log = [];
    public function record(string $event): void { $this->log[] = $event; }
}

abstract class OrderExporter
{
    public function __construct(
        protected readonly OrderRepository $orders,
        protected readonly AuditTrail $audit,
    ) {}

    /** Workflow shape — locked. Subclasses only choose `format()`. */
    final public function export(int $userId): string
    {
        $orders = $this->orders->ordersFor($userId);
        $rows = array_map(
            static fn (array $o): array => ['order_id' => $o['id'], 'total_pounds' => round($o['total'] / 100, 2)],
            $orders,
        );
        $payload = $this->format($rows);
        $this->audit->record(sprintf('exported user=%d format=%s rows=%d', $userId, $this->formatName(), count($rows)));
        return $payload;
    }

    abstract protected function formatName(): string;
    /** @param list<array<string,mixed>> $rows */
    abstract protected function format(array $rows): string;
}

final class CsvOrderExporter extends OrderExporter
{
    protected function formatName(): string { return 'csv'; }
    protected function format(array $rows): string
    {
        if ($rows === []) return '';
        $headers = array_keys($rows[0]);
        $out = implode(',', $headers) . "\n";
        foreach ($rows as $row) $out .= implode(',', array_values($row)) . "\n";
        return $out;
    }
}

final class JsonOrderExporter extends OrderExporter
{
    protected function formatName(): string { return 'json'; }
    protected function format(array $rows): string
    {
        return json_encode($rows, JSON_THROW_ON_ERROR);
    }
}

// ---- assertions -------------------------------------------------------------

$repo = new InMemoryOrders([
    42 => [['id' => 1, 'total' => 1234], ['id' => 2, 'total' => 9900]],
]);

$audit = new AuditTrail();
$csv = (new CsvOrderExporter($repo, $audit))->export(42);
pdp_assert_eq("order_id,total_pounds\n1,12.34\n2,99\n", $csv, 'csv contains transformed rows');
pdp_assert_eq(['exported user=42 format=csv rows=2'], $audit->log, 'csv export audited');

$audit = new AuditTrail();
$json = (new JsonOrderExporter($repo, $audit))->export(42);
pdp_assert_eq('[{"order_id":1,"total_pounds":12.34},{"order_id":2,"total_pounds":99}]', $json, 'json contains transformed rows');
pdp_assert_eq(['exported user=42 format=json rows=2'], $audit->log, 'json export audited');

// base workflow tested via an anonymous subclass — no real format involved
$audit = new AuditTrail();
$captured = [];
$probe = new class($repo, $audit, $captured) extends OrderExporter {
    public function __construct(OrderRepository $r, AuditTrail $a, public array &$captured)
    {
        parent::__construct($r, $a);
    }
    protected function formatName(): string { return 'probe'; }
    protected function format(array $rows): string { $this->captured = $rows; return 'OK'; }
};
$out = $probe->export(42);
pdp_assert_eq('OK', $out, 'workflow returns whatever format() returns');
pdp_assert_eq([['order_id' => 1, 'total_pounds' => 12.34], ['order_id' => 2, 'total_pounds' => 99.00]], $probe->captured, 'transform happened before format()');
pdp_assert_eq(1, count($audit->log), 'audit ran after format()');

pdp_done();
