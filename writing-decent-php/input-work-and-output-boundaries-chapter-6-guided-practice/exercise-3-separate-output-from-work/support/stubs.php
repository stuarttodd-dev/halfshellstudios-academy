<?php
declare(strict_types=1);

final class OrderItemCollection
{
    /** @param list<object> $items */
    public function __construct(private array $items) {}

    public function count(): int
    {
        return count($this->items);
    }
}

final class Order
{
    public OrderItemCollection $items;

    /** @var array<int, self> */
    private static array $store = [];

    public function __construct(
        public readonly int $id,
        public readonly int $totalInPence,
        OrderItemCollection $items,
    ) {
        $this->items     = $items;
        self::$store[$id] = $this;
    }

    public static function find(int $id): self
    {
        if (! isset(self::$store[$id])) {
            throw new RuntimeException("Order {$id} not found");
        }

        return self::$store[$id];
    }
}
