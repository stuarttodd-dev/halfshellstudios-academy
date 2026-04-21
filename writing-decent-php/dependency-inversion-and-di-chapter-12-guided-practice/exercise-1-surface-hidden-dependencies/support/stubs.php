<?php
declare(strict_types=1);

final class Tenant
{
    public function __construct(public readonly int $id) {}
}

final class TenantContext
{
    public static ?Tenant $current = null;

    public static function current(): Tenant
    {
        return self::$current ?? throw new RuntimeException('No tenant in context.');
    }
}

final class Queue
{
    /** @var list<array{queue: string, payload: array<string, mixed>}> */
    public static array $jobs = [];

    /** @param array<string, mixed> $payload */
    public static function push(string $queue, array $payload): void
    {
        self::$jobs[] = ['queue' => $queue, 'payload' => $payload];
    }

    public static function reset(): void
    {
        self::$jobs = [];
    }
}

final class Logger
{
    /** @var list<string> */
    public static array $entries = [];

    public static function info(string $message): void
    {
        self::$entries[] = "[info] {$message}";
    }

    public static function reset(): void
    {
        self::$entries = [];
    }
}
