<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * The use case's typed result. It carries raw domain values — no
 * formatted strings, no JSON. That keeps it useful for any presenter:
 * an HTTP response, a CSV row, a CLI table, a notification email.
 */
final class OrderSummary
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $totalInPence,
        public readonly int $itemCount,
    ) {}
}

/**
 * Pure work: fetch, project, return a typed value. No JSON, no
 * formatting, no presentation choices.
 */
final class GenerateOrderSummary
{
    public function run(int $orderId): OrderSummary
    {
        $order = Order::find($orderId);

        return new OrderSummary(
            orderId:      $order->id,
            totalInPence: $order->totalInPence,
            itemCount:    $order->items->count(),
        );
    }
}

/**
 * Output boundary. Owns the reference format, the currency symbol,
 * the decimal places, and JSON encoding. Swap this for an HtmlPresenter
 * or CsvPresenter without touching the use case.
 */
final class OrderSummaryJsonPresenter
{
    private const REFERENCE_PREFIX     = 'ORDER-';
    private const REFERENCE_PAD_LENGTH = 6;
    private const CURRENCY_SYMBOL      = '£';

    public function present(OrderSummary $summary): string
    {
        return json_encode([
            'reference' => self::REFERENCE_PREFIX
                . str_pad((string) $summary->orderId, self::REFERENCE_PAD_LENGTH, '0', STR_PAD_LEFT),
            'total'     => self::CURRENCY_SYMBOL . number_format($summary->totalInPence / 100, 2),
            'items'     => $summary->itemCount,
        ]);
    }
}

/* ---------- driver (output matches starter.php) ---------- */

new Order(id: 1,    totalInPence: 1234,    items: new OrderItemCollection([(object) [], (object) []]));
new Order(id: 42,   totalInPence: 99_950,  items: new OrderItemCollection([(object) []]));
new Order(id: 9999, totalInPence: 250_000, items: new OrderItemCollection(array_fill(0, 12, (object) [])));

$useCase   = new GenerateOrderSummary();
$presenter = new OrderSummaryJsonPresenter();

foreach ([1, 42, 9999] as $id) {
    echo $presenter->present($useCase->run($id)), "\n";
}
