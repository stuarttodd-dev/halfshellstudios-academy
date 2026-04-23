<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface Logger
{
    public function info(string $message): void;
}

/** A trivial in-memory logger so the test can inspect what was written. */
final class ArrayLogger implements Logger
{
    /** @var list<string> */
    public array $lines = [];
    public function __construct(public readonly string $channel) {}
    public function info(string $message): void { $this->lines[] = "[{$this->channel}] {$message}"; }
}

/** Real-world adapter — kept here as a sketch for shape, not exercised. */
final class FileLogger implements Logger
{
    public function __construct(public readonly string $path) {}
    public function info(string $message): void { file_put_contents($this->path, $message . "\n", FILE_APPEND); }
}

interface LoggerFactory
{
    public function for(string $channel): Logger;
}

/**
 * The default factory knows the channel-to-path map. Callers ask for a
 * logger by channel name and never see a file path.
 */
final class DefaultLoggerFactory implements LoggerFactory
{
    /** @param array<string, string> $channelPaths */
    public function __construct(private readonly array $channelPaths) {}

    public function for(string $channel): Logger
    {
        if (!isset($this->channelPaths[$channel])) {
            throw new \RuntimeException("Unknown log channel: {$channel}");
        }
        return new FileLogger($this->channelPaths[$channel]);
    }
}

final class OrderProcessor
{
    public function __construct(private readonly LoggerFactory $loggers) {}
    public function process(object $order): void
    {
        $this->loggers->for('orders')->info("Processing order {$order->id}");
    }
}

final class PaymentProcessor
{
    public function __construct(private readonly LoggerFactory $loggers) {}
    public function process(object $payment): void
    {
        $this->loggers->for('payments')->info("Processing payment {$payment->id}");
    }
}

// ---- assertions -------------------------------------------------------------

/**
 * Test factory: hands back ArrayLogger instances by channel name so we can
 * inspect what each processor wrote without touching the file system.
 */
final class TestLoggerFactory implements LoggerFactory
{
    /** @var array<string, ArrayLogger> */
    public array $loggers = [];
    public function for(string $channel): Logger
    {
        return $this->loggers[$channel] ??= new ArrayLogger($channel);
    }
}

$factory = new TestLoggerFactory();
(new OrderProcessor($factory))->process((object) ['id' => 7]);
(new PaymentProcessor($factory))->process((object) ['id' => 99]);

pdp_assert_eq(['[orders] Processing order 7'],     $factory->loggers['orders']->lines,   'OrderProcessor writes to the orders channel');
pdp_assert_eq(['[payments] Processing payment 99'], $factory->loggers['payments']->lines, 'PaymentProcessor writes to the payments channel');

// And the factory *itself* is independently testable without any processor.
$default = new DefaultLoggerFactory(['orders' => '/tmp/o.log', 'payments' => '/tmp/p.log']);
$logger  = $default->for('orders');
pdp_assert_true($logger instanceof FileLogger, 'default factory returns a FileLogger');
pdp_assert_eq('/tmp/o.log', $logger->path, 'default factory carries the right channel path');
pdp_assert_throws(\RuntimeException::class, fn () => $default->for('unknown'), 'default factory rejects unknown channels');

pdp_done();
