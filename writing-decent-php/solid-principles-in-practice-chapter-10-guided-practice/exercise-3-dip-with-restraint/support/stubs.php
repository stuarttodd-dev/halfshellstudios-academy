<?php
declare(strict_types=1);

namespace {

    if (! function_exists('env')) {
        function env(string $key, mixed $default = null): mixed
        {
            return $_ENV[$key] ?? $default;
        }
    }

    /**
     * Tiny stand-in for the global `DB::table()->find() / ->insert()` façade.
     * Backed by an array per table; query methods are deliberately minimal.
     */
    final class DB
    {
        /** @var array<string, list<array<string, mixed>>> */
        public static array $tables = [];

        public static function reset(): void
        {
            self::$tables = ['orders' => [], 'refunds' => []];
        }

        public static function table(string $name): DBQuery
        {
            self::$tables[$name] ??= [];
            return new DBQuery($name);
        }
    }

    final class DBQuery
    {
        public function __construct(private string $table) {}

        public function find(int $id): object
        {
            foreach (DB::$tables[$this->table] as $row) {
                if (($row['id'] ?? null) === $id) {
                    return (object) $row;
                }
            }

            throw new \RuntimeException("No row {$id} in {$this->table}");
        }

        /** @param array<string, mixed> $values */
        public function insert(array $values): void
        {
            DB::$tables[$this->table][] = $values;
        }
    }
}

namespace Stripe {

    /**
     * The real `\Stripe\StripeClient` exposes `$client->refunds->create([...])`
     * and a few hundred other endpoints. We mimic only the shape the lesson
     * uses so the starter compiles and runs without the real SDK.
     */
    final class StripeClient
    {
        public Refunds $refunds;

        public function __construct(public readonly string $apiKey)
        {
            $this->refunds = new Refunds($this);
        }
    }

    final class Refunds
    {
        /** @var list<array<string, mixed>> */
        public array $created = [];

        public function __construct(private StripeClient $client) {}

        /** @param array<string, mixed> $params */
        public function create(array $params): object
        {
            $id = sprintf('re_%05d', count($this->created) + 1);
            $this->created[] = $params + ['id' => $id, 'api_key_used' => $this->client->apiKey];
            return (object) ['id' => $id];
        }
    }
}
