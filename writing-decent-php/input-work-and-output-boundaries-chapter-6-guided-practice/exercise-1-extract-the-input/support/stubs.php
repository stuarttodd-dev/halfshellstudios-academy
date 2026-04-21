<?php
declare(strict_types=1);

/**
 * Tiny stand-ins for the Laravel-shaped types used in the lesson snippet so
 * starter.php and solution.php can run as plain PHP scripts.
 */

final class Request
{
    /** @param array<string, mixed> $payload */
    public function __construct(private array $payload) {}

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }
}

final class JsonResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public readonly array $data,
        public readonly int   $status = 200,
    ) {}
}

/**
 * Recording fake for the `DB::table('bookings')->where(...)->update(...)`
 * fluent chain. We capture the last update so both starter and solution
 * can be compared on observable behaviour.
 */
final class DB
{
    /** @var list<array{table: string, id: int, values: array<string, mixed>}> */
    public static array $updates = [];

    public static function reset(): void
    {
        self::$updates = [];
    }

    public static function table(string $table): DbTableQuery
    {
        return new DbTableQuery($table);
    }
}

final class DbTableQuery
{
    private int $whereId;

    public function __construct(private string $table) {}

    public function where(string $column, int $id): self
    {
        if ($column !== 'id') {
            throw new RuntimeException("only stub-supports where('id', …)");
        }

        $this->whereId = $id;

        return $this;
    }

    /** @param array<string, mixed> $values */
    public function update(array $values): void
    {
        DB::$updates[] = [
            'table'  => $this->table,
            'id'     => $this->whereId,
            'values' => $values,
        ];
    }
}
