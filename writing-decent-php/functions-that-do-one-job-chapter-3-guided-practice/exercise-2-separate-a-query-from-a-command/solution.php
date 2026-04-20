<?php
declare(strict_types=1);

require_once __DIR__ . '/support/InMemoryApiKeyRepository.php';

/**
 * Pure queries — never mutate state.
 */
final class ApiKeyReader
{
    public function __construct(private InMemoryApiKeyRepository $repository) {}

    public function findApiKey(int $keyId): ?array
    {
        return $this->repository->findById($keyId);
    }

    public function isApiKeyValid(int $keyId): bool
    {
        $key = $this->repository->findById($keyId);

        if ($key === null) {
            return false;
        }

        return $key['expires_at'] > time();
    }
}

/**
 * Commands — always mutate state, never return information.
 */
final class ApiKeyUsageRecorder
{
    public function __construct(private InMemoryApiKeyRepository $repository) {}

    public function recordUsage(int $keyId): void
    {
        $this->repository->incrementUsageCount($keyId);
        $this->repository->updateLastUsedAt($keyId, time());
    }

    public function recordValidationAttempt(int $keyId): void
    {
        $this->repository->incrementValidationCount($keyId);
    }
}

$repository = new InMemoryApiKeyRepository([
    1 => ['id' => 1, 'token' => 'abc', 'expires_at' => time() + 3600],
]);

$reader   = new ApiKeyReader($repository);
$recorder = new ApiKeyUsageRecorder($repository);

$reader->isApiKeyValid(1);
$recorder->recordValidationAttempt(1);

$reader->isApiKeyValid(1);
$recorder->recordValidationAttempt(1);

$apiKey = $reader->findApiKey(1);
$recorder->recordUsage(1);
var_export($apiKey);
echo "\n";

echo "usage_count:      " . $repository->usageCount(1)      . "\n";
echo "validation_count: " . $repository->validationCount(1) . "\n";
