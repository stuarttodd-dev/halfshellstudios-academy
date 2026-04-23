<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** A tiny in-memory line source so we don't need a real file in tests. */
final class StringLineSource
{
    public int $linesRead = 0;
    /** @param list<string> $lines */
    public function __construct(public readonly array $lines) {}

    /** @return \Generator<int, string> */
    public function lines(): \Generator
    {
        foreach ($this->lines as $line) {
            $this->linesRead++;
            yield $line;
        }
    }
}

final class CsvReader
{
    public function __construct(private readonly StringLineSource $source) {}

    /** Yields associative rows lazily. @return \Generator<int, array<string,string>> */
    public function rows(): \Generator
    {
        $headers = null;
        foreach ($this->source->lines() as $line) {
            $cells = str_getcsv($line);
            if ($headers === null) { $headers = $cells; continue; }
            yield array_combine($headers, $cells);
        }
    }
}

/**
 * Generic filter: takes any iterable + a predicate, yields matching items.
 *
 * @template T
 * @param iterable<T> $items
 * @param callable(T): bool $predicate
 * @return \Generator<int, T>
 */
function filter(iterable $items, callable $predicate): \Generator
{
    foreach ($items as $item) {
        if ($predicate($item)) yield $item;
    }
}

// ---- assertions -------------------------------------------------------------

$source = new StringLineSource([
    'id,status',
    '1,active',
    '2,pending',
    '3,active',
    '4,inactive',
    '5,active',
]);

$reader = new CsvReader($source);

$activeIds = [];
foreach (filter($reader->rows(), static fn (array $r) => $r['status'] === 'active') as $row) {
    $activeIds[] = $row['id'];
}
pdp_assert_eq(['1', '3', '5'], $activeIds, 'filter yields only active rows');
pdp_assert_eq(6, $source->linesRead, 'all 6 lines read (1 header + 5 rows) after consuming everything');

// laziness: early break stops reading
$source2 = new StringLineSource([
    'id,status',
    '1,active', '2,active', '3,active', '4,active', '5,active', '6,active',
]);
$reader2 = new CsvReader($source2);

foreach ($reader2->rows() as $row) {
    if ($row['id'] === '2') break;
}
pdp_assert_eq(3, $source2->linesRead, 'broke after id=2 -> only 3 lines read (header + 2 rows)');

pdp_done();
