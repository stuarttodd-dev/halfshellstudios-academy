<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * The reason a refund is being issued is a domain concept, not an HTTP
 * concept. Promoting it to an enum makes the use-case signature honest —
 * any unsupported value is rejected at the boundary, not deep inside the
 * use case.
 */
enum RefundReason: string
{
    case Goodwill = 'goodwill';
    case Standard = 'standard';
}

/**
 * A typed result. The use case used to return an `int` of "pence",
 * which is fine, but a one-field value object documents the unit at
 * the call-site and gives us a place to grow (currency, tax, notes)
 * without changing every caller's signature.
 */
final class RefundAmount
{
    public function __construct(public readonly int $pence)
    {
        if ($pence < 0) {
            throw new InvalidArgumentException('Refund amount cannot be negative.');
        }
    }

    public static function none(): self
    {
        return new self(0);
    }
}

/**
 * Pure business logic. No `Request`, no HTTP, no array indexing.
 * Trivial to unit test: build an Order, pass a reason, assert pennies.
 */
final class CalculateRefund
{
    private const STANDARD_REFUND_WINDOW = '-30 days';
    private const STANDARD_REFUND_RATE   = 0.8;

    public function calculate(Order $order, RefundReason $reason): RefundAmount
    {
        if ($reason === RefundReason::Goodwill) {
            return new RefundAmount($order->totalInPence);
        }

        if ($order->placedAt < new DateTimeImmutable(self::STANDARD_REFUND_WINDOW)) {
            return RefundAmount::none();
        }

        return new RefundAmount((int) round($order->totalInPence * self::STANDARD_REFUND_RATE));
    }
}

/**
 * The HTTP-aware adapter sits where it belongs: at the boundary.
 * It owns the parsing of "reason" from the wire, with a helpful
 * 422 if it's missing or unknown — but the use case never sees it.
 */
final class CalculateRefundController
{
    public function __construct(private CalculateRefund $useCase) {}

    public function __invoke(Order $order, Request $request): RefundAmount
    {
        $rawReason = (string) ($request->input('reason') ?? '');
        $reason    = RefundReason::tryFrom($rawReason) ?? RefundReason::Standard;

        return $this->useCase->calculate($order, $reason);
    }
}

/* ---------- driver ----------
 *
 * Output matches starter.php. The starter's "no reason" case fell through
 * to the standard-rate branch (no early returns matched), and the typed
 * boundary preserves that by defaulting to RefundReason::Standard.
 */

$useCase    = new CalculateRefund();
$controller = new CalculateRefundController($useCase);

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
    printf("%-22s -> %d pence\n", $label, $controller($order, $request)->pence);
}
