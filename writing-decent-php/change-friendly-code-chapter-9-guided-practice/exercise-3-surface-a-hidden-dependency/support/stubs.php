<?php
declare(strict_types=1);

/**
 * Globals the starter reaches for. They are deliberately stateful so we
 * can demonstrate exactly how invasive the global-state setup becomes
 * when you want to test the starter, and how cleanly the solution
 * sidesteps it all.
 */

if (! function_exists('config')) {
    /** @var array<string, mixed> */
    $GLOBALS['__config'] = [];

    function config(string $key): mixed
    {
        return $GLOBALS['__config'][$key] ?? null;
    }
}

final class DB
{
    /** @var list<array{table: string, values: array<string, mixed>}> */
    public static array $inserts = [];

    public static function reset(): void
    {
        self::$inserts = [];
    }

    public static function table(string $table): DbQuery
    {
        return new DbQuery($table);
    }
}

final class DbQuery
{
    public function __construct(private string $table) {}

    /** @param array<string, mixed> $values */
    public function insert(array $values): void
    {
        DB::$inserts[] = ['table' => $this->table, 'values' => $values];
    }
}

final class Logger
{
    /** @var list<string> */
    public static array $messages = [];

    public static function reset(): void
    {
        self::$messages = [];
    }

    public static function log(string $message): void
    {
        self::$messages[] = $message;
    }
}
