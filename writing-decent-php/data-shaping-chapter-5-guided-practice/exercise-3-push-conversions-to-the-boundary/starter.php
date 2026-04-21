<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class OrderController
{
    public function __construct(private OrderService $service) {}

    public function store(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        $order   = $this->service->placeOrder($payload);

        return response()->json(['id' => $order->id, 'reference' => $order->reference]);
    }
}

final class OrderService
{
    public function __construct(private InMemoryOrderRepository $repository) {}

    public function placeOrder(array $payload): Order
    {
        if (! isset($payload['customer_id']) || ! is_int($payload['customer_id'])) {
            throw new InvalidArgumentException('customer_id required');
        }
        if (! isset($payload['items']) || ! is_array($payload['items']) || $payload['items'] === []) {
            throw new InvalidArgumentException('items required');
        }

        $order = Order::draft(new CustomerId($payload['customer_id']));

        foreach ($payload['items'] as $item) {
            if (! isset($item['product_id'], $item['quantity'])) {
                throw new InvalidArgumentException('item missing fields');
            }

            $order->addLine(new OrderLine(
                productId: new ProductId((int) $item['product_id']),
                quantity:  (int) $item['quantity'],
            ));
        }

        $this->repository->save($order);

        return $order;
    }
}

/* ---------- driver ---------- */

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
