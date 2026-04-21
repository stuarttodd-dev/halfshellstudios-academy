<?php
declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use App\OrderFulfilment\Listeners\StartFulfilmentOnOrderConfirmed;
use App\OrderFulfilment\Persistence\FulfilmentRepository;
use App\OrderPlacement\Events\EventBus;
use App\OrderPlacement\Events\OrderConfirmedEvent;
use App\OrderPlacement\Events\ShippableLine;
use App\OrderPlacement\UseCases\PlaceOrder;

/**
 * The composition root — the only place that knows about both modules.
 * It wires Fulfilment's listener to the bus, then hands the bus to
 * Placement. Placement publishes; Fulfilment reacts; neither side has
 * to import from the other.
 */
final class InProcessEventBus implements EventBus
{
    /** @var list<callable(OrderConfirmedEvent): void> */
    private array $listeners = [];

    public function subscribe(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function publish(object $event): void
    {
        foreach ($this->listeners as $listener) {
            $listener($event);
        }
    }
}

$fulfilmentRepo = new FulfilmentRepository();
$listener       = new StartFulfilmentOnOrderConfirmed($fulfilmentRepo);

$bus = new InProcessEventBus();
$bus->subscribe([$listener, 'handle']);

$placement = new PlaceOrder($bus);

$orderId = $placement->place('10 Downing St, London SW1A 2AA', [
    new ShippableLine(productReference: 'BOOK-001', quantity: 2, weightInGrams: 600),
    new ShippableLine(productReference: 'PEN-007',  quantity: 5, weightInGrams: 50),
]);

printf("placed order id          = %d\n",  $orderId->value);
printf("fulfilment record count  = %d\n",  $fulfilmentRepo->count());

$fulfilment = $fulfilmentRepo->find($orderId->value);
printf("fulfilment status        = %s\n", $fulfilment->status);
printf("fulfilment line count    = %d\n", count($fulfilment->lines));
printf("fulfilment first line    = %s × %d (%dg)\n",
    $fulfilment->lines[0]['productReference'],
    $fulfilment->lines[0]['quantity'],
    $fulfilment->lines[0]['weightInGrams'],
);
