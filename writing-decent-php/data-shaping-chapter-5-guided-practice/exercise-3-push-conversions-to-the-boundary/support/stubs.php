<?php
declare(strict_types=1);

/**
 * Tiny stand-ins for the Laravel-shaped types in the lesson snippet so
 * both starter and solution can run as plain PHP scripts.
 */

final class Request
{
    /** @param array<string, mixed> $payload */
    public function __construct(private array $payload) {}

    public function json(): self
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->payload;
    }
}

final class JsonResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(public readonly array $data) {}
}

function response(): object
{
    return new class {
        /** @param array<string, mixed> $data */
        public function json(array $data): JsonResponse
        {
            return new JsonResponse($data);
        }
    };
}

final class CustomerId
{
    public function __construct(public readonly int $value) {}
}

final class ProductId
{
    public function __construct(public readonly int $value) {}
}

final class OrderLine
{
    public function __construct(
        public readonly ProductId $productId,
        public readonly int       $quantity,
    ) {}
}

final class Order
{
    /** @var list<OrderLine> */
    public array $lines = [];

    public ?int    $id        = null;
    public ?string $reference = null;

    private function __construct(public readonly CustomerId $customerId) {}

    public static function draft(CustomerId $customerId): self
    {
        return new self($customerId);
    }

    public function addLine(OrderLine $line): void
    {
        $this->lines[] = $line;
    }
}

final class InMemoryOrderRepository
{
    private int $nextId = 1000;

    public function save(Order $order): void
    {
        $order->id        = $this->nextId++;
        $order->reference = sprintf('ORD-%05d', $order->id);
    }
}
