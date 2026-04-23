<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: this Repository is NOT earning its keep — and has two
 * reasonable resolutions, depending on what we are trying to buy.
 *
 *   final class UserRepository
 *   {
 *       public function find(int $id): ?User { return User::find($id); }
 *   }
 *
 * The wrapper is one method that calls one method. It does not:
 *   - introduce a domain interface (it leaks Eloquent's `User` model);
 *   - simplify the call site;
 *   - decouple the use case from Eloquent (the return type *is* Eloquent);
 *   - make testing easier (a stub of this class is no easier to write
 *     than a `User::factory()->make()`).
 *
 * It only adds a layer.
 *
 * Two reasonable next moves:
 *
 *   (a) DELETE IT. Inject the Eloquent model directly. No layer means no
 *       maintenance cost, no second source of truth.
 *
 *   (b) GROW IT INTO A REAL REPOSITORY: a domain interface that
 *       returns a *domain* `User` (a value object / aggregate, not the
 *       Eloquent model), with methods that are actually used by the
 *       use case (`find`, `byEmail`, etc.). The Eloquent implementation
 *       is one of multiple — alongside InMemory.
 *
 * The choice depends on whether the use case ever wants to be tested
 * without Eloquent, or run against a non-Eloquent backend. This
 * `solution.php` shows option (b) — the "grow it" version — and walks
 * through the contract a real repository establishes.
 */

final class UserId
{
    public function __construct(public readonly int $value) {}
}

/** Domain User — NOT the Eloquent model. */
final class User
{
    public function __construct(
        public readonly UserId $id,
        public readonly string $email,
        public readonly string $name,
    ) {}
}

interface UserRepository
{
    public function find(UserId $id): ?User;
    public function byEmail(string $email): ?User;
}

/** In-memory — fully testable, no DB at all. */
final class InMemoryUserRepository implements UserRepository
{
    /** @param array<int, User> $usersById */
    public function __construct(private array $usersById = []) {}
    public function add(User $user): void { $this->usersById[$user->id->value] = $user; }
    public function find(UserId $id): ?User { return $this->usersById[$id->value] ?? null; }
    public function byEmail(string $email): ?User
    {
        foreach ($this->usersById as $u) if ($u->email === $email) return $u;
        return null;
    }
}

// ---- assertions -------------------------------------------------------------

$repo = new InMemoryUserRepository();
$repo->add(new User(new UserId(1), 'alice@example.com', 'Alice'));
$repo->add(new User(new UserId(2), 'bob@example.com',   'Bob'));

pdp_assert_eq('Alice', $repo->find(new UserId(1))?->name,       'find by id');
pdp_assert_eq(null,    $repo->find(new UserId(999)),            'find returns null on miss');
pdp_assert_eq('Bob',   $repo->byEmail('bob@example.com')?->name, 'byEmail returns the user');
pdp_assert_eq(null,    $repo->byEmail('nobody@example.com'),    'byEmail returns null on miss');

pdp_done('(The starter wrapper was the WRONG kind of Repository. See the comment block for the two correct moves.)');
