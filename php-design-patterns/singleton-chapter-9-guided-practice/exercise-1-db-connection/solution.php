<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * Replace the classic Singleton (`Db::instance()`) with an interface
 * the rest of the system depends on, plus an implementation the
 * container resolves once.
 *
 * Production: bind `DatabaseConnection` to `PdoDatabaseConnection`.
 * Tests: bind to `SqliteDatabaseConnection` (or any in-memory fake).
 */

interface DatabaseConnection
{
    /** @return list<array<string, mixed>> */
    public function query(string $sql): array;
}

final class PdoDatabaseConnection implements DatabaseConnection
{
    public function __construct(private readonly \PDO $pdo) {}
    public function query(string $sql): array
    {
        $stmt = $this->pdo->query($sql);
        return $stmt === false ? [] : $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/** Test/dev double — backed by SQLite in-memory. */
final class SqliteDatabaseConnection implements DatabaseConnection
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE counters (name TEXT PRIMARY KEY, value INTEGER)');
    }
    public function query(string $sql): array
    {
        $stmt = $this->pdo->query($sql);
        return $stmt === false ? [] : $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function execute(string $sql): void { $this->pdo->exec($sql); }
}

/** Tiny container that resolves a single bound instance per type. */
final class Container
{
    /** @var array<string, object> */
    private array $bindings = [];
    public function bind(string $type, object $instance): void { $this->bindings[$type] = $instance; }
    public function get(string $type): object
    {
        return $this->bindings[$type] ?? throw new \RuntimeException("Unbound: {$type}");
    }
}

/** Caller declares its dependency in the constructor — no static accessor in sight. */
final class CounterService
{
    public function __construct(private readonly DatabaseConnection $db) {}
    public function valueOf(string $name): int
    {
        $rows = $this->db->query("SELECT value FROM counters WHERE name = '{$name}'");
        return (int) ($rows[0]['value'] ?? 0);
    }
}

// ---- assertions -------------------------------------------------------------

// Production wiring: one instance bound in the container.
$container = new Container();
$container->bind(DatabaseConnection::class, new SqliteDatabaseConnection());

/** @var SqliteDatabaseConnection $db */
$db = $container->get(DatabaseConnection::class);
$db->execute("INSERT INTO counters (name, value) VALUES ('clicks', 7)");

$service = new CounterService($db);
pdp_assert_eq(7, $service->valueOf('clicks'), 'service uses the bound DB connection');

// Test-time injection: a different DatabaseConnection slots straight in.
$test = new SqliteDatabaseConnection();
$test->execute("INSERT INTO counters (name, value) VALUES ('clicks', 99)");
pdp_assert_eq(99, (new CounterService($test))->valueOf('clicks'), 'test-time DB has its own state');

// Production state is untouched by the test injection.
pdp_assert_eq(7, $service->valueOf('clicks'), 'production state untouched (no global state)');

// Container hands out the same instance every time (single instance per binding).
pdp_assert_true(
    $container->get(DatabaseConnection::class) === $container->get(DatabaseConnection::class),
    'container returns the same instance across calls (single instance, not Singleton)',
);

pdp_done();
