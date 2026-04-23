<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — `findUser($id)` returning a "null user".
 *
 * Null Object earns its keep when callers want to *do nothing*
 * (NullLogger ignores the call, NullMailer drops the email) and
 * removing the no-op feels like progress. It is a *behavioural* stand-
 * in.
 *
 * `findUser` is a *lookup*. The caller almost always needs to branch
 * on "did we find one?" before doing anything meaningful — render a
 * profile vs render 404, charge a customer vs throw, etc. A
 * NullUser hides that branch behind sentinel values:
 *
 *   if ($user->name === '') { ... }
 *
 * which is just `=== null` with extra steps and easier to forget.
 *
 * Below: return `?User` and let the caller decide. PHP's nullable
 * types and `??` make this clean.
 */

final class User
{
    public function __construct(public readonly int $id, public readonly string $name) {}
}

final class UserDirectory
{
    /** @var array<int, User> */
    private array $byId;

    public function __construct(User ...$users)
    {
        $this->byId = [];
        foreach ($users as $u) $this->byId[$u->id] = $u;
    }

    public function find(int $id): ?User { return $this->byId[$id] ?? null; }
}

$dir = new UserDirectory(new User(1, 'Alex'), new User(2, 'Beth'));

pdp_assert_eq('Alex', $dir->find(1)?->name, 'found user');
pdp_assert_eq(null,    $dir->find(99),       'missing user is genuinely absent');

// caller branches honestly
$render = static fn (?User $u) => $u === null ? '404 Not Found' : "Profile for {$u->name}";
pdp_assert_eq('Profile for Alex', $render($dir->find(1)),  'render found');
pdp_assert_eq('404 Not Found',    $render($dir->find(99)), 'render missing');

pdp_done('Null Object was the wrong answer here — see the comment block.');
