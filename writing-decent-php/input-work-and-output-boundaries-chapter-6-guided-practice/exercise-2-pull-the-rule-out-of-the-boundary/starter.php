<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class CalculateRefund
{
    public function calculate(Order $order, Request $request): int
    {
        $reason = $request->input('reason');
        $base   = $order->totalInPence;

        if ($reason === 'goodwill') {
            return $base;
        }
        if ($order->placedAt < new DateTimeImmutable('-30 days')) {
            return 0;
        }
        return (int) round($base * 0.8);
    }
}

/* ---------- driver ---------- */

$useCase = new CalculateRefund();

$recent = new Order(id: 1, totalInPence: 10_000, placedAt: new DateTimeImmutable('-5 days'));
$old    = new Order(id: 2, totalInPence: 10_000, placedAt: new DateTimeImmutable('-90 days'));

$cases = [
    'goodwill on recent'   => [$recent, new Request(['reason' => 'goodwill'])],
    'goodwill on old'      => [$old,    new Request(['reason' => 'goodwill'])],
    'standard on recent'   => [$recent, new Request(['reason' => 'standard'])],
    'standard on old'      => [$old,    new Request(['reason' => 'standard'])],
    'no reason on recent'  => [$recent, new Request([])],
];

foreach ($cases as $label => [$order, $request]) {
    printf("%-22s -> %d pence\n", $label, $useCase->calculate($order, $request));
}
