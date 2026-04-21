<?php
declare(strict_types=1);

require_once __DIR__ . '/subject.php';

/**
 * Identical to the test from Exercise 1. Not even an assertion was
 * touched — that is the contract of a rename-only refactor.
 */

/** @return array<string, array{order: array<string, mixed>, expected: string}> */
function characterisation_cases(): array
{
    return [
        'plain non-GB, no discount' => [
            'order'    => ['id' => 1, 'country' => 'FR', 'items' => [['name' => 'Tea', 'price' => 3, 'qty' => 2]]],
            'expected' => "Invoice #1\nTea: 3 x 2\nTotal: 6",
        ],
        'GB applies 20% VAT' => [
            'order'    => ['id' => 2, 'country' => 'GB', 'items' => [['name' => 'Tea', 'price' => 3, 'qty' => 2]]],
            'expected' => "Invoice #2\nTea: 3 x 2\nTotal: 7.2",
        ],
        'GB + discount stack (VAT first, then 10% off)' => [
            'order'    => [
                'id'       => 3,
                'country'  => 'GB',
                'discount' => 'NEW10',
                'items'    => [
                    ['name' => 'Tea',  'price' => 3, 'qty' => 2],
                    ['name' => 'Cake', 'price' => 5, 'qty' => 1],
                ],
            ],
            'expected' => "Invoice #3\nTea: 3 x 2\nCake: 5 x 1\nTotal: 11.88",
        ],
        'empty items still renders header + total' => [
            'order'    => ['id' => 4, 'country' => 'GB', 'items' => []],
            'expected' => "Invoice #4\nTotal: 0",
        ],
        'non-GB + discount applies discount only' => [
            'order'    => ['id' => 5, 'country' => 'IE', 'discount' => 'NEW10', 'items' => [['name' => 'Tea', 'price' => 3, 'qty' => 2]]],
            'expected' => "Invoice #5\nTea: 3 x 2\nTotal: 5.4",
        ],
    ];
}

function run_characterisation_test(): void
{
    $failures = [];
    foreach (characterisation_cases() as $label => $case) {
        $actual = generateInvoice($case['order']);
        if ($actual !== $case['expected']) {
            $failures[] = sprintf("  [%s] FAILED\n    expected:\n      %s\n    actual:\n      %s",
                $label,
                str_replace("\n", "\n      ", $case['expected']),
                str_replace("\n", "\n      ", $actual),
            );
        } else {
            echo "  [{$label}] ok\n";
        }
    }
    if ($failures !== []) {
        echo "\nCharacterisation test FAILED after rename — names changed semantics, that is a bug.\n" . implode("\n", $failures) . "\n";
        exit(1);
    }
    echo "characterisation test: PASS (5/5 cases pinned, post-rename)\n";
}

run_characterisation_test();
