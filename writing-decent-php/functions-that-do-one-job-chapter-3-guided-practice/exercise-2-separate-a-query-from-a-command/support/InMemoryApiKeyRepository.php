<?php
declare(strict_types=1);

final class InMemoryApiKeyRepository
{
    /** @var array<int, array<string, mixed>> */
    private array $keys;

    /** @var array<int, int> */
    private array $usageCounts = [];

    /** @var array<int, int> */
    private array $validationCounts = [];

    /** @var array<int, int> */
    private array $lastUsedAt = [];

    /** @param array<int, array<string, mixed>> $seed */
    public function __construct(array $seed = [])
    {
        $this->keys = $seed;
    }

    public function findById(int $keyId): ?array
    {
        return $this->keys[$keyId] ?? null;
    }

    public function incrementUsageCount(int $keyId): void
    {
        $this->usageCounts[$keyId] = ($this->usageCounts[$keyId] ?? 0) + 1;
    }

    public function incrementValidationCount(int $keyId): void
    {
        $this->validationCounts[$keyId] = ($this->validationCounts[$keyId] ?? 0) + 1;
    }

    public function updateLastUsedAt(int $keyId, int $timestamp): void
    {
        $this->lastUsedAt[$keyId] = $timestamp;
    }

    public function usageCount(int $keyId): int
    {
        return $this->usageCounts[$keyId] ?? 0;
    }

    public function validationCount(int $keyId): int
    {
        return $this->validationCounts[$keyId] ?? 0;
    }
}
