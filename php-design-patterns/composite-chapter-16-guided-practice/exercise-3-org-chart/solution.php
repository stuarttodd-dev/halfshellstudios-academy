<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface OrgNode
{
    public function name(): string;
    public function headcount(): int;
}

final class Employee implements OrgNode
{
    public function __construct(public readonly string $employeeName) {}
    public function name(): string { return $this->employeeName; }
    public function headcount(): int { return 1; }
}

final class Department implements OrgNode
{
    /** @param list<OrgNode> $members */
    public function __construct(
        public readonly string $departmentName,
        public readonly array $members = [],
    ) {}

    public function name(): string { return $this->departmentName; }

    public function headcount(): int
    {
        return array_sum(array_map(static fn (OrgNode $m) => $m->headcount(), $this->members));
    }
}

// ---- assertions -------------------------------------------------------------

$alice = new Employee('Alice');
$bob   = new Employee('Bob');
$carol = new Employee('Carol');

pdp_assert_eq(1, $alice->headcount(), 'employee always counts 1');

$frontend = new Department('Frontend', [$alice, $bob]);
pdp_assert_eq(2, $frontend->headcount(), 'leaf-only department');

$backend = new Department('Backend', [$carol]);
$engineering = new Department('Engineering', [$frontend, $backend, new Employee('Dani')]);

pdp_assert_eq(4, $engineering->headcount(), 'nested departments + a direct employee');
pdp_assert_eq('Engineering', $engineering->name(), 'composite name');

// caller depends only on OrgNode — no instanceof
$nodes = [$alice, $frontend, $engineering];
$total = 0;
foreach ($nodes as $n) $total += $n->headcount();
pdp_assert_eq(7, $total, 'caller treats leaves and composites uniformly');

// empty department
pdp_assert_eq(0, (new Department('Empty'))->headcount(), 'empty department -> 0');

pdp_done();
