<?php
declare(strict_types=1);

final class Request
{
    /** @param array<string, string> $headers */
    public function __construct(public readonly array $headers = []) {}
}

final class JsonResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public readonly array $data,
        public readonly int   $status = 200,
    ) {}
}

final class User
{
    public function __construct(
        public readonly int    $id,
        public readonly string $email,
    ) {}
}

/* ---------- in-memory infrastructure ---------- */

final class InMemoryUserDirectory
{
    /** @var array<string, User> */
    public array $byToken = [
        'tok_alice' =>  null, // populated below
    ];

    public function __construct()
    {
        $this->byToken = [
            'tok_alice' => new User(id: 9001, email: 'alice@example.com'),
        ];
    }

    public function userForToken(string $token): ?User
    {
        return $this->byToken[$token] ?? null;
    }
}

final class InMemoryOrderStore
{
    /** @var array<int, list<array{id: int, total_pence: int}>> */
    public array $byUserId = [
        9001 => [
            ['id' => 1, 'total_pence' => 1_500],
            ['id' => 2, 'total_pence' => 4_200],
        ],
    ];

    /** @return list<array{id: int, total_pence: int}> */
    public function ordersFor(int $userId): array
    {
        return $this->byUserId[$userId] ?? [];
    }
}
