<?php
declare(strict_types=1);

final class Order
{
    public function __construct(
        public readonly int               $id,
        public readonly int               $totalInPence,
        public readonly DateTimeImmutable $placedAt,
    ) {}
}

final class Request
{
    /** @param array<string, mixed> $payload */
    public function __construct(private array $payload) {}

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }
}
