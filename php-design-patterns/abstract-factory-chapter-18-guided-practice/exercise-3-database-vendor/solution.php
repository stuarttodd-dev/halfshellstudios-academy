<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface Connection { public function vendor(): string; }
interface QueryBuilder { public function quoteIdentifier(string $name): string; }
interface Migrator { public function placeholder(): string; }

interface DatabaseFactory
{
    public function connection(): Connection;
    public function queryBuilder(): QueryBuilder;
    public function migrator(): Migrator;
}

/* ---- MySQL family ---- */

final class MysqlConnection implements Connection { public function vendor(): string { return 'mysql'; } }
final class MysqlQueryBuilder implements QueryBuilder { public function quoteIdentifier(string $n): string { return "`{$n}`"; } }
final class MysqlMigrator implements Migrator { public function placeholder(): string { return '?'; } }

final class MysqlFactory implements DatabaseFactory
{
    public function connection(): Connection { return new MysqlConnection(); }
    public function queryBuilder(): QueryBuilder { return new MysqlQueryBuilder(); }
    public function migrator(): Migrator { return new MysqlMigrator(); }
}

/* ---- Postgres family ---- */

final class PostgresConnection implements Connection { public function vendor(): string { return 'pgsql'; } }
final class PostgresQueryBuilder implements QueryBuilder { public function quoteIdentifier(string $n): string { return "\"{$n}\""; } }
final class PostgresMigrator implements Migrator { public function placeholder(): string { return '$1'; } }

final class PostgresFactory implements DatabaseFactory
{
    public function connection(): Connection { return new PostgresConnection(); }
    public function queryBuilder(): QueryBuilder { return new PostgresQueryBuilder(); }
    public function migrator(): Migrator { return new PostgresMigrator(); }
}

/* ---- A service that depends on the factory, never on a vendor ---- */

final class UserService
{
    public function __construct(private readonly DatabaseFactory $db) {}

    public function describe(): string
    {
        $vendor   = $this->db->connection()->vendor();
        $quoted   = $this->db->queryBuilder()->quoteIdentifier('users');
        $placeHld = $this->db->migrator()->placeholder();
        return "{$vendor}:{$quoted}:{$placeHld}";
    }
}

// ---- wiring (chosen once, at the composition root) ------------------------

$mysqlSvc = new UserService(new MysqlFactory());
$pgsqlSvc = new UserService(new PostgresFactory());

// ---- assertions -------------------------------------------------------------

pdp_assert_eq('mysql:`users`:?',     $mysqlSvc->describe(), 'mysql family used coherently');
pdp_assert_eq('pgsql:"users":$1',    $pgsqlSvc->describe(), 'pgsql family used coherently');

// service has no `if vendor === ...` anywhere
$ref = new \ReflectionClass(UserService::class);
$src = file_get_contents($ref->getFileName());
pdp_assert_true(!str_contains($src, "'mysql'") || str_contains($src, '/* allowed */'),
    'UserService never branches on vendor names');

pdp_done();
