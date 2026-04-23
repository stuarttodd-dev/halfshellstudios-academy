<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class User
{
    /** @param list<string> $roles */
    public function __construct(
        public readonly string $id,
        public readonly bool $superAdmin = false,
        public readonly array $roles = [],
        /** @var list<string> */
        public readonly array $teamMemberIds = [],
    ) {}
    public function isSuperAdmin(): bool { return $this->superAdmin; }
    public function hasRole(string $r): bool { return in_array($r, $this->roles, true); }
    public function isOwnerOf(object $r): bool { return $r->ownerId === $this->id; }
    public function isInTeamWith(string $userId): bool { return in_array($userId, $this->teamMemberIds, true); }
}

final class Resource
{
    public function __construct(
        public readonly string $id,
        public readonly string $ownerId,
        public readonly string $module = 'default',
    ) {}
}

interface PermissionRule
{
    public function check(User $user, string $action, Resource $resource, callable $next): bool;
}

final class PermissionChain
{
    /** @param list<PermissionRule> $rules */
    public function __construct(private readonly array $rules) {}

    public function check(User $user, string $action, Resource $resource): bool
    {
        $deny = static fn (): bool => false;
        $next = $deny;
        foreach (array_reverse($this->rules) as $rule) {
            $current = $next;
            $next = static fn () => $rule->check($user, $action, $resource, $current);
        }
        return $next();
    }
}

final class SuperAdminRule implements PermissionRule
{
    public function check(User $u, string $a, Resource $r, callable $next): bool
    {
        return $u->isSuperAdmin() ?: $next();
    }
}

final class OwnerRule implements PermissionRule
{
    public function check(User $u, string $a, Resource $r, callable $next): bool
    {
        return $u->isOwnerOf($r) ?: $next();
    }
}

final class TeamViewRule implements PermissionRule
{
    public function check(User $u, string $a, Resource $r, callable $next): bool
    {
        return ($a === 'view' && $u->isInTeamWith($r->ownerId)) ?: $next();
    }
}

final class AdminRoleRule implements PermissionRule
{
    /** @param list<string> $allowedActions */
    public function __construct(private readonly array $allowedActions = ['view', 'edit']) {}
    public function check(User $u, string $a, Resource $r, callable $next): bool
    {
        return ($u->hasRole('admin') && in_array($a, $this->allowedActions, true)) ?: $next();
    }
}

/** Module-specific custom rule, slotted in by wiring without touching others. */
final class BillingModuleAccountantRule implements PermissionRule
{
    public function check(User $u, string $a, Resource $r, callable $next): bool
    {
        return ($r->module === 'billing' && $u->hasRole('accountant') && in_array($a, ['view', 'export'], true))
            ? true
            : $next();
    }
}

// ---- wiring ----------------------------------------------------------------

$baseRules = [
    new SuperAdminRule(),
    new OwnerRule(),
    new TeamViewRule(),
    new AdminRoleRule(),
];

$default = new PermissionChain($baseRules);
$billing = new PermissionChain([new BillingModuleAccountantRule(), ...$baseRules]);

// ---- assertions -------------------------------------------------------------

$super  = new User('1', superAdmin: true);
$owner  = new User('2');
$team   = new User('3', teamMemberIds: ['2']);
$admin  = new User('4', roles: ['admin']);
$accnt  = new User('5', roles: ['accountant']);
$random = new User('6');

$res        = new Resource('r1', ownerId: '2');
$billingRes = new Resource('r2', ownerId: '2', module: 'billing');

pdp_assert_true($default->check($super,  'delete', $res), 'super admin allowed anything');
pdp_assert_true($default->check($owner,  'delete', $res), 'owner allowed anything');
pdp_assert_true($default->check($team,   'view',   $res), 'team member can view');
pdp_assert_true(!$default->check($team,  'edit',   $res), 'team member cannot edit');
pdp_assert_true($default->check($admin,  'edit',   $res), 'admin can edit');
pdp_assert_true(!$default->check($admin, 'delete', $res), 'admin cannot delete');
pdp_assert_true(!$default->check($random,'view',   $res), 'random user denied');

// custom billing handler slotted in at the wiring layer
pdp_assert_true(!$default->check($accnt, 'export', $billingRes), 'accountant denied by default chain');
pdp_assert_true($billing->check($accnt,  'export', $billingRes), 'accountant allowed in billing module');
pdp_assert_true($billing->check($super,  'delete', $billingRes), 'super admin still wins in billing');

pdp_done();
