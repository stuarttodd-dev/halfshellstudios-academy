<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — `Specification` for `$user->isActive`.
 *
 * Specification earns its keep when:
 *   - rules are composed at runtime (AND / OR / NOT pieces),
 *   - the same predicate is reused across selection AND validation,
 *   - the rule space grows with new criteria.
 *
 * `is the user active?` is a single boolean field. Wrapping it in
 * `class IsActiveSpecification` so callers can write
 * `$spec->isSatisfiedBy($user)` instead of `$user->isActive` is pure
 * ceremony.
 *
 * Below: a tiny User and a one-liner filter.
 */

final class User
{
    public function __construct(public readonly string $name, public readonly bool $isActive) {}
}

$users = [
    new User('Alex', true),
    new User('Beth', false),
    new User('Cara', true),
];

$active = array_values(array_filter($users, static fn (User $u) => $u->isActive));

pdp_assert_eq(['Alex', 'Cara'], array_map(static fn (User $u) => $u->name, $active), 'active users');

pdp_done('Specification was the wrong answer here — see the comment block.');
