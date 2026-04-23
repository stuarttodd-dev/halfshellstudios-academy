<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface Config
{
    public function get(string $key): mixed;
}

final class ArrayConfig implements Config
{
    /** @param array<string, mixed> $values */
    public function __construct(private array $values) {}
    public function get(string $key): mixed { return $this->values[$key] ?? null; }
}

interface TenantProvider
{
    public function tenantId(): string;
}

/**
 * Per-request tenant provider — at the boundary (HTTP middleware,
 * console kernel, …) we set this once for the request.
 */
final class StaticTenantProvider implements TenantProvider
{
    public function __construct(private readonly string $tenantId) {}
    public function tenantId(): string { return $this->tenantId; }
}

/**
 * The container resolves Config per request based on the tenant id.
 * Production: read from disk; cache per tenant for the process.
 *
 * Note: this is still ONE instance per (process, tenant) — not a
 * Singleton. The container owns lifetime; no class hides global state.
 */
final class TenantConfigResolver
{
    /** @var array<string, Config> */
    private array $perTenant = [];

    /** @param callable(string): Config $loader */
    public function __construct(private $loader) {}

    public function for(TenantProvider $provider): Config
    {
        $id = $provider->tenantId();
        return $this->perTenant[$id] ??= ($this->loader)($id);
    }
}

final class FeatureFlagService
{
    public function __construct(
        private readonly TenantConfigResolver $configs,
        private readonly TenantProvider $tenant,
    ) {}

    public function isEnabled(string $flag): bool
    {
        return (bool) $this->configs->for($this->tenant)->get("features.{$flag}");
    }
}

// ---- assertions -------------------------------------------------------------

$loader = static fn (string $tenantId): Config => match ($tenantId) {
    'acme'    => new ArrayConfig(['features.beta' => true,  'features.legacy' => false]),
    'globex'  => new ArrayConfig(['features.beta' => false, 'features.legacy' => true]),
    default   => new ArrayConfig([]),
};

$resolver = new TenantConfigResolver($loader);

// Two tenants in the same process get two independent configs.
$acme   = new FeatureFlagService($resolver, new StaticTenantProvider('acme'));
$globex = new FeatureFlagService($resolver, new StaticTenantProvider('globex'));

pdp_assert_eq(true,  $acme->isEnabled('beta'),   'acme has beta enabled');
pdp_assert_eq(false, $globex->isEnabled('beta'), 'globex does not have beta enabled');
pdp_assert_eq(true,  $globex->isEnabled('legacy'), 'globex has legacy enabled');
pdp_assert_eq(false, $acme->isEnabled('legacy'),   'acme does not have legacy enabled');

// And the resolver memoises per-tenant: one Config per tenant, not per call.
pdp_assert_true(
    $resolver->for(new StaticTenantProvider('acme')) === $resolver->for(new StaticTenantProvider('acme')),
    'resolver returns the same Config instance for the same tenant id',
);
pdp_assert_true(
    $resolver->for(new StaticTenantProvider('acme')) !== $resolver->for(new StaticTenantProvider('globex')),
    'resolver returns DIFFERENT Config instances for different tenants',
);

pdp_done();
