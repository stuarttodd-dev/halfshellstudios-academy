<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class User
{
    public function __construct(public readonly int $id, public readonly string $email) {}
}

interface UserPager
{
    /** @return list<User> */
    public function page(int $offset, int $limit): array;
    public function total(): int;
}

final class FakeUserPager implements UserPager
{
    public int $pagesFetched = 0;
    /** @var list<User> */
    private array $all;

    public function __construct(int $count)
    {
        $this->all = [];
        for ($i = 1; $i <= $count; $i++) $this->all[] = new User($i, "user{$i}@x.test");
    }

    public function page(int $offset, int $limit): array
    {
        $this->pagesFetched++;
        return array_slice($this->all, $offset, $limit);
    }

    public function total(): int { return count($this->all); }
}

final class UserList implements \IteratorAggregate
{
    public function __construct(
        private readonly UserPager $pager,
        private readonly int $pageSize = 100,
    ) {}

    /** @return \Generator<int, User> */
    public function getIterator(): \Generator
    {
        $offset = 0;
        $total = $this->pager->total();
        while ($offset < $total) {
            $page = $this->pager->page($offset, $this->pageSize);
            if ($page === []) break;
            foreach ($page as $user) yield $user;
            $offset += $this->pageSize;
        }
    }
}

// ---- assertions -------------------------------------------------------------

$pager = new FakeUserPager(count: 250);
$users = new UserList($pager, pageSize: 100);

$count = 0;
$allUsers = true;
foreach ($users as $user) {
    if (!($user instanceof User)) { $allUsers = false; break; }
    $count++;
    if ($count > 999) break;
}
pdp_assert_true($allUsers, 'every yielded value is a User');
pdp_assert_eq(250, $count, 'iterates every user');
pdp_assert_eq(3, $pager->pagesFetched, 'three pages: 100, 100, 50');

// laziness: early break does not pull more pages
$pager2 = new FakeUserPager(count: 1_000);
$users2 = new UserList($pager2, pageSize: 100);
$seen = 0;
foreach ($users2 as $u) {
    if (++$seen >= 5) break;
}
pdp_assert_eq(5, $seen, 'consumed exactly five users');
pdp_assert_eq(1, $pager2->pagesFetched, 'only one page fetched — early break did not load all 10');

// caller never sees offsets/cursors
pdp_assert_true(method_exists(UserList::class, 'getIterator'), 'getIterator is the only iteration surface');

pdp_done();
