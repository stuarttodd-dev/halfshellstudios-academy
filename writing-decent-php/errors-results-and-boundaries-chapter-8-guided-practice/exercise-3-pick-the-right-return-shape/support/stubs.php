<?php
declare(strict_types=1);

final class Customer
{
    public function __construct(
        public readonly int    $id,
        public readonly string $email,
        public readonly string $name,
        public readonly bool   $marketingOptIn,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id:              (int)    $row['id'],
            email:           (string) $row['email'],
            name:            (string) $row['name'],
            marketingOptIn:  (bool)   $row['marketing_opt_in'],
        );
    }
}

/**
 * Tiny `DB::table()->where()->first()` fake. Returns a row array or null.
 */
final class DB
{
    /** @var array<string, array<string, mixed>> keyed by email */
    private static array $customers = [
        'alice@example.com' => ['id' => 1, 'email' => 'alice@example.com', 'name' => 'Alice', 'marketing_opt_in' => true],
        'bob@example.com'   => ['id' => 2, 'email' => 'bob@example.com',   'name' => 'Bob',   'marketing_opt_in' => false],
    ];

    public static function table(string $table): DbQuery
    {
        if ($table !== 'customers') {
            throw new RuntimeException("only stub-supports customers table");
        }

        return new DbQuery(self::$customers);
    }
}

final class DbQuery
{
    private string $whereColumn = '';
    private mixed  $whereValue  = null;

    /** @param array<string, array<string, mixed>> $rowsByEmail */
    public function __construct(private array $rowsByEmail) {}

    public function where(string $column, mixed $value): self
    {
        $this->whereColumn = $column;
        $this->whereValue  = $value;
        return $this;
    }

    /** @return array<string, mixed>|null */
    public function first(): ?array
    {
        if ($this->whereColumn !== 'email') {
            throw new RuntimeException("only stub-supports where('email', …)");
        }

        return $this->rowsByEmail[$this->whereValue] ?? null;
    }
}

final class JsonResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(public readonly array $data, public readonly int $status = 200) {}
}

/**
 * Recording fake for the marketing mailer in call site 2.
 */
final class MarketingMailer
{
    /** @var list<string> */
    public static array $sent = [];

    public static function reset(): void
    {
        self::$sent = [];
    }

    public function sendCampaignTo(Customer $customer): void
    {
        self::$sent[] = $customer->email;
    }
}
