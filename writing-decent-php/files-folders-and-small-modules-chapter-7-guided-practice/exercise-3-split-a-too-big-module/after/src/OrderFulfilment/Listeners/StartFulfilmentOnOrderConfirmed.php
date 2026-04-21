<?php
declare(strict_types=1);

namespace App\OrderFulfilment\Listeners;

use App\OrderFulfilment\Domain\FulfilmentOrder;
use App\OrderFulfilment\Persistence\FulfilmentRepository;
use App\OrderPlacement\Events\OrderConfirmedEvent;

/**
 * The only point of contact in the entire codebase between the two
 * modules. OrderFulfilment imports `OrderConfirmedEvent` from Placement;
 * the dependency arrow points one way, never the other.
 */
final class StartFulfilmentOnOrderConfirmed
{
    public function __construct(private FulfilmentRepository $repository) {}

    public function handle(OrderConfirmedEvent $event): void
    {
        $lines = array_map(
            static fn ($line): array => [
                'productReference' => $line->productReference,
                'quantity'         => $line->quantity,
                'weightInGrams'    => $line->weightInGrams,
            ],
            $event->lines,
        );

        $this->repository->save(new FulfilmentOrder(
            orderId:         $event->orderId->value,
            shippingAddress: $event->shippingAddress,
            lines:           $lines,
        ));
    }
}
