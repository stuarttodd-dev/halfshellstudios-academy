<?php
declare(strict_types=1);

final class Request
{
    /** @param array<string, mixed> $payload */
    public function __construct(private array $payload) {}

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
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
    public function __construct(public readonly array $data, public readonly int $status = 200) {}
}

final class Log
{
    /** @var list<string> */
    public static array $errors = [];

    public static function reset(): void
    {
        self::$errors = [];
    }

    public static function error(string $message): void
    {
        self::$errors[] = $message;
    }
}

/** Domain failure modes the use case is allowed to raise. */
final class CustomerNotFoundException        extends \DomainException {}
final class OrderAlreadyInvoicedException    extends \DomainException {}
final class InvalidInvoiceInputException     extends \DomainException {}

/**
 * The use case. The behaviour is keyed on the input so both starter and
 * solution drivers can reproduce identical inner behaviour and we can
 * focus on what the *controller* does with each outcome.
 */
final class CreateInvoice
{
    public function handle(Request $request): int
    {
        return match ($request->input('case')) {
            'happy'              => 4242,
            'customer-missing'   => throw new CustomerNotFoundException('Customer 99 not found'),
            'already-invoiced'   => throw new OrderAlreadyInvoicedException('Order 17 already has invoice 4001'),
            'bad-input'          => throw new InvalidInvoiceInputException('amount_in_pence must be > 0'),
            'system-failure'     => throw new \RuntimeException('database connection lost'),
            default              => throw new \LogicException('unknown test case'),
        };
    }
}
