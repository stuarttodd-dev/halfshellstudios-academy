<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Boundary types: arrays come in here and never go any further.
 */
final class OrderLineRequest
{
    public function __construct(
        public readonly ProductId $productId,
        public readonly int       $quantity,
    ) {}

    /** @param array<string, mixed> $item */
    public static function fromArray(array $item): self
    {
        if (! isset($item['product_id'], $item['quantity'])) {
            throw new InvalidArgumentException('item missing fields');
        }

        return new self(
            productId: new ProductId((int) $item['product_id']),
            quantity:  (int) $item['quantity'],
        );
    }
}

final class OrderRequest
{
    /**
     * @param list<OrderLineRequest> $lines
     */
    public function __construct(
        public readonly CustomerId $customerId,
        public readonly array      $lines,
    ) {
        if ($lines === []) {
            throw new InvalidArgumentException('items required');
        }
    }

    public static function fromHttpRequest(Request $request): self
    {
        $payload = $request->json()->all();

        if (! isset($payload['customer_id']) || ! is_int($payload['customer_id'])) {
            throw new InvalidArgumentException('customer_id required');
        }
        if (! isset($payload['items']) || ! is_array($payload['items']) || $payload['items'] === []) {
            throw new InvalidArgumentException('items required');
        }

        $lines = array_map(
            static fn (array $item): OrderLineRequest => OrderLineRequest::fromArray($item),
            $payload['items'],
        );

        return new self(new CustomerId($payload['customer_id']), $lines);
    }
}

final class OrderController
{
    public function __construct(private OrderService $service) {}

    public function store(Request $request): JsonResponse
    {
        $order = $this->service->placeOrder(OrderRequest::fromHttpRequest($request));

        return response()->json(['id' => $order->id, 'reference' => $order->reference]);
    }
}

/**
 * Speaks only in typed objects. No `array` in or out, no validation,
 * no `isset()`. The shape it receives is already trustworthy.
 */
final class OrderService
{
    public function __construct(private InMemoryOrderRepository $repository) {}

    public function placeOrder(OrderRequest $request): Order
    {
        $order = Order::draft($request->customerId);

        foreach ($request->lines as $line) {
            $order->addLine(new OrderLine(
                productId: $line->productId,
                quantity:  $line->quantity,
            ));
        }

        $this->repository->save($order);

        return $order;
    }
}

/* ---------- driver (identical to starter.php) ---------- */

$controller = new OrderController(new OrderService(new InMemoryOrderRepository()));

$happy = new Request([
    'customer_id' => 42,
    'items'       => [
        ['product_id' => 1, 'quantity' => 2],
        ['product_id' => 7, 'quantity' => 1],
    ],
]);

$response = $controller->store($happy);
printf("happy path: id=%d reference=%s lines=%d\n",
    $response->data['id'],
    $response->data['reference'],
    2,
);

$invalidPayloads = [
    'missing customer_id' => ['items'       => [['product_id' => 1, 'quantity' => 1]]],
    'string customer_id'  => ['customer_id' => '42', 'items' => [['product_id' => 1, 'quantity' => 1]]],
    'missing items'       => ['customer_id' => 42],
    'empty items'         => ['customer_id' => 42, 'items' => []],
    'item missing fields' => ['customer_id' => 42, 'items' => [['product_id' => 1]]],
];

foreach ($invalidPayloads as $label => $payload) {
    try {
        $controller->store(new Request($payload));
        printf("%-20s -> NO EXCEPTION\n", $label);
    } catch (InvalidArgumentException $e) {
        printf("%-20s -> %s\n", $label, $e->getMessage());
    }
}
