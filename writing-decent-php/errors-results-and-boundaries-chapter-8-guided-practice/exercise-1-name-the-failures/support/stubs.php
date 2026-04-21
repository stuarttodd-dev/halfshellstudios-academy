<?php
declare(strict_types=1);

/**
 * Tiny in-memory account stand-ins so starter and solution can run end-to-end.
 */
final class Account
{
    public function __construct(
        public readonly int $id,
        public int          $balanceInPence,
    ) {}
}

final class InMemoryAccountRepository
{
    /** @var array<int, Account> */
    private array $accounts;

    /** @param list<Account> $accounts */
    public function __construct(array $accounts)
    {
        foreach ($accounts as $account) {
            $this->accounts[$account->id] = $account;
        }
    }

    public function byId(int $id): ?Account
    {
        return $this->accounts[$id] ?? null;
    }

    public function debit(Account $account, int $amountInPence): void
    {
        $account->balanceInPence -= $amountInPence;
    }

    public function credit(Account $account, int $amountInPence): void
    {
        $account->balanceInPence += $amountInPence;
    }
}

final class Request
{
    /** @param array<string, mixed> $payload */
    public function __construct(private array $payload) {}

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }
}

final class JsonResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(public readonly array $data, public readonly int $status = 200) {}
}
