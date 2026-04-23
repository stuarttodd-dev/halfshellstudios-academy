<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Intrinsic state — shared across many posts. */
final class AuthorProfile
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $avatarUrl,
        public readonly string $title,
    ) {}
}

interface AuthorRepository { public function find(string $id): AuthorProfile; }

final class AuthorRegistry
{
    /** @var array<string, AuthorProfile> */
    private array $cache = [];

    public function __construct(private readonly AuthorRepository $repo) {}

    public function get(string $authorId): AuthorProfile
    {
        return $this->cache[$authorId] ??= $this->repo->find($authorId);
    }

    public function size(): int { return count($this->cache); }
}

/** Extrinsic state — what differs per post — plus a reference to the shared profile. */
final class Post
{
    public function __construct(
        public readonly AuthorProfile $author,
        public readonly string $body,
    ) {}
}

final class StubRepo implements AuthorRepository
{
    public int $loadCount = 0;
    /** @param array<string, AuthorProfile> $byId */
    public function __construct(public array $byId = []) {}
    public function find(string $id): AuthorProfile
    {
        $this->loadCount++;
        return $this->byId[$id] ?? throw new \RuntimeException("unknown {$id}");
    }
}

// ---- assertions -------------------------------------------------------------

$repo = new StubRepo([
    'a1' => new AuthorProfile('a1', 'Alice', '/avatars/a1.png', 'Senior Dev'),
    'a2' => new AuthorProfile('a2', 'Bob',   '/avatars/a2.png', 'Junior Dev'),
]);
$registry = new AuthorRegistry($repo);

// 1000 posts, only 2 distinct authors
$posts = [];
for ($i = 0; $i < 1000; $i++) {
    $posts[] = new Post($registry->get($i % 2 === 0 ? 'a1' : 'a2'), body: "post {$i}");
}

pdp_assert_eq(2, $repo->loadCount, 'repo touched once per author');
pdp_assert_eq(2, $registry->size(), 'registry holds two shared profiles');

// identity, not equality
pdp_assert_true($posts[0]->author === $posts[2]->author, 'two posts by same author share the SAME profile object');
pdp_assert_true($posts[0]->author !== $posts[1]->author, 'different authors -> different objects');

// measurement: posts are tiny because the heavy bits live in 2 shared objects
$beforeBytes = strlen(serialize($posts));
$inlinedAuthor = static fn (Post $p) => (object) [
    'authorId' => $p->author->id, 'authorName' => $p->author->name,
    'authorAvatarUrl' => $p->author->avatarUrl, 'authorTitle' => $p->author->title,
    'body' => $p->body,
];
$afterBytes = strlen(serialize(array_map($inlinedAuthor, $posts)));
pdp_assert_true($beforeBytes < $afterBytes, sprintf('shared profiles save bytes (shared=%d, inlined=%d)', $beforeBytes, $afterBytes));

pdp_done();
