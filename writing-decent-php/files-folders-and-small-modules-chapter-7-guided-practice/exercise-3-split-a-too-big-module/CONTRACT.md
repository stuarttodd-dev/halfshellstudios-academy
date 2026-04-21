# Contract between `OrderPlacement` and `OrderFulfilment`

`OrderPlacement` owns the lifecycle of an order from cart to "confirmed and
paid": entities (`Order`, `OrderLine`, `OrderId`), the placement use cases
(`PlaceOrder`, `AddToCartHandler`), pricing/discount/VAT rules, and the
payment hand-off. When an order is fully paid, `OrderPlacement` publishes a
`OrderConfirmedEvent` carrying an `OrderId` and a list of immutable
`ShippableLine` snapshots (product reference, quantity, weight, delivery
address). Past that point, `OrderPlacement` neither knows nor cares what
happens next — it does **not** import a single class from `OrderFulfilment`.
`OrderFulfilment` owns everything from "we have a confirmed order" onwards:
generating shipping labels, choosing carriers, tracking, returns. It
subscribes to `OrderConfirmedEvent`, builds its own `FulfilmentOrder`
projection, and writes to its own `FulfilmentRepository`. Communication is
one-directional and event-driven: `OrderPlacement` publishes, `OrderFulfilment`
listens. Removing the listener should still leave both modules fully
buildable and independently testable — that is the test for whether the
split is honest.
