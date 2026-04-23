<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface Logger
{
    public function info(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
}

final class StdoutLogger implements Logger
{
    /** @var list<string> */
    public array $lines = [];
    public function info(string $message, array $context = []): void  { $this->lines[] = "INFO: {$message}"; }
    public function error(string $message, array $context = []): void { $this->lines[] = "ERROR: {$message}"; }
}

/**
 * Null Object: a Logger that's always safe to call. Lets the rest of
 * the codebase drop `if ($this->logger !== null)` everywhere.
 */
final class NullLogger implements Logger
{
    public function info(string $message, array $context = []): void { /* no-op */ }
    public function error(string $message, array $context = []): void { /* no-op */ }
}

final class OrderService
{
    public function __construct(private readonly Logger $logger = new NullLogger()) {}

    public function place(string $sku): string
    {
        $this->logger->info("placing order for {$sku}");
        // ... real work ...
        return "order-{$sku}";
    }
}

// ---- assertions -------------------------------------------------------------

// production: real logger
$prod = new StdoutLogger();
$svc = new OrderService($prod);
$svc->place('book-1');
pdp_assert_eq(['INFO: placing order for book-1'], $prod->lines, 'real logger receives the call');

// quick script: no logger needed, no null checks needed inside the service
$quiet = new OrderService();
pdp_assert_eq('order-book-2', $quiet->place('book-2'), 'works without a logger and without throwing');

// the value of the pattern is structural: OrderService::place() simply calls
// $this->logger->info(...) without "is the logger present?" branching.

pdp_done();
