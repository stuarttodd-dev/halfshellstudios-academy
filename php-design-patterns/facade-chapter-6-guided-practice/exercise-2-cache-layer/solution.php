<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: a Facade is the WRONG answer here.
 *
 * `Cache::remember('key', $ttl, $loader)` is already a facade — that
 * is exactly what Laravel's Cache *is*. It already presents a single,
 * named operation in front of multiple drivers, locks, expiry, etc.
 *
 * Adding `MyCacheFacade::remember(...)` that calls `Cache::remember(...)`
 * is **relabelling**. It moves the call from one named entry point to
 * another while changing nothing about the cohesion, hiding, or
 * caller's API. It gives every existing seam a cosmetic detour.
 *
 * When could a wrapper earn its place?
 *
 *   - **Domain language.** `UserCache::forget(int $userId)` is more
 *     specific than `Cache::forget("users.{$id}")`. The wrapper is no
 *     longer a "facade over the cache"; it is a *use-case service*
 *     that happens to use the cache. Different intent, same pattern
 *     boundary.
 *
 *   - **Decoupling.** A `CacheStore` interface decouples the use case
 *     from Laravel-specific globals. That is **DI through a domain
 *     interface**, not a facade.
 *
 * For "I want to call Cache::remember in one line", the right answer
 * is "call Cache::remember in one line".
 */

// To make the point concretely, here is the relabelling we are NOT doing:
//
// final class MyCacheFacade {
//     public static function remember(string $k, int $ttl, callable $loader): mixed {
//         return Cache::remember($k, $ttl, $loader);
//     }
// }
//
// And here is the thing that DOES earn its place: a use-case service
// in domain language that uses the existing facade internally.

interface UserCache
{
    public function getOrLoad(int $userId, callable $loader): object;
    public function forget(int $userId): void;
}

final class InMemoryUserCache implements UserCache
{
    /** @var array<int, object> */
    private array $store = [];
    public function getOrLoad(int $userId, callable $loader): object
    {
        return $this->store[$userId] ??= $loader();
    }
    public function forget(int $userId): void
    {
        unset($this->store[$userId]);
    }
}

// ---- assertions -------------------------------------------------------------

$cache = new InMemoryUserCache();
$loadCount = 0;
$loader = function () use (&$loadCount) { $loadCount++; return (object) ['id' => 42, 'name' => 'Alice']; };

$user1 = $cache->getOrLoad(42, $loader);
$user2 = $cache->getOrLoad(42, $loader);
pdp_assert_eq(1, $loadCount, 'second getOrLoad does not invoke the loader');
pdp_assert_true($user1 === $user2, 'returns the same instance');

$cache->forget(42);
$cache->getOrLoad(42, $loader);
pdp_assert_eq(2, $loadCount, 'after forget, the loader runs again');

pdp_done('(Facade was the wrong answer for the original snippet — see the comment block.)');
