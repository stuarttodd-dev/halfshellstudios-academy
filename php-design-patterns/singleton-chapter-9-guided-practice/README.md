# Chapter 9 — Singleton (and alternatives) (guided practice)

The chapter is mostly about *not* writing classic Singletons. The
right tool is almost always "an interface, an implementation, and a
container that resolves it once". The trap is the case where there
is no state at all — keep the static.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — DB connection singleton | Stateful `Db::instance()` reading `getenv` | **Replace** with `DatabaseConnection` interface + container binding |
| 2 — `Math::clampToRange` | Pure, stateless helper | **Trap.** Static is fine — there's nothing to inject |
| 3 — Multi-tenant config | One `Config::instance()` cannot represent multiple tenants per process | **Replace** with a `Config` interface + per-tenant resolver |

---

## Exercise 1 — DB connection

### Before

```php
final class Db
{
    private static ?Db $instance = null;
    private PDO $pdo;
    private function __construct() { $this->pdo = new PDO(getenv('DB_DSN'), getenv('DB_USER'), getenv('DB_PASS')); }
    public static function instance(): self { return self::$instance ??= new self(); }
    public function pdo(): PDO { return $this->pdo; }
}
```

### After

```php
interface DatabaseConnection { public function query(string $sql): array; }

final class PdoDatabaseConnection    implements DatabaseConnection { /* PDO-backed */ }
final class SqliteDatabaseConnection implements DatabaseConnection { /* in-memory, used in tests */ }

$container = new Container();
$container->bind(DatabaseConnection::class, new PdoDatabaseConnection($pdo));

final class CounterService
{
    public function __construct(private DatabaseConnection $db) {}
    public function valueOf(string $name): int { /* uses $this->db->query(...) */ }
}
```

### What the refactor buys

- The dependency is **declared** in `CounterService`'s constructor —
  no hidden `Db::instance()`.
- The container holds **one instance** of `DatabaseConnection`. The
  application still only opens one PDO connection — that's a wiring
  decision, not a global hack.
- Tests inject a `SqliteDatabaseConnection` without touching global
  state, and production state is unaffected.

---

## Exercise 2 — `Math::clampToRange` (the trap)

### Verdict — replacing it would be a downgrade

The reasons we usually replace Singletons:

- they hide a dependency at the call site;
- they hold **mutable state** tests cannot reset;
- they couple consumers to a global accessor.

`Math::clampToRange(int $n, int $min, int $max): int` is **pure**: no
state, no I/O, no clock, no random, no global config. There is nothing
to inject. Forcing every caller to wire up a `Math` instance to call
a deterministic function would *add* coupling and gain nothing.

Stateless, side-effect-free helpers are exactly what static methods
are for. The fact that it's static is not the problem; if the project
has many tiny helpers like this, the only refactor worth considering
is **grouping** (`App\Math::clamp(...)` namespace) for autoload tidiness.

---

## Exercise 3 — Multi-tenant config

### Before

A single static `Config::instance()` representing the whole process.
Once a second tenant arrives in the same process, the static is wrong.

### After

```php
interface Config { public function get(string $key): mixed; }

final class TenantConfigResolver
{
    /** @var array<string, Config> */
    private array $perTenant = [];
    public function __construct(private $loader) {}
    public function for(TenantProvider $p): Config
    {
        return $this->perTenant[$p->tenantId()] ??= ($this->loader)($p->tenantId());
    }
}

final class FeatureFlagService
{
    public function __construct(private TenantConfigResolver $configs, private TenantProvider $tenant) {}
    public function isEnabled(string $flag): bool { return (bool) $this->configs->for($this->tenant)->get("features.{$flag}"); }
}
```

### What the refactor buys

- One `Config` per **tenant**, not per process.
- Per-request middleware writes the tenant id to the
  `TenantProvider` once; everything downstream is consistent.
- Memoised: one `Config` instance per tenant for the process — no
  re-reading on every call.
- No static, no global, no surprise mutations.

---

## Chapter rubric

For each non-trap exercise:

- interface that consumers depend on (no static accessors)
- implementation registered as a single instance in the container (or
  manually wired at the composition root)
- callers declaring the dependency in their constructors
- tests injecting an alternative implementation without touching global state

For the trap: explain why pure static utilities have nothing to inject.

---

## How to run

```bash
cd php-design-patterns/singleton-chapter-9-guided-practice
php exercise-1-db-connection/solution.php
php exercise-2-math-utilities/solution.php
php exercise-3-multi-tenant-config/solution.php
```
