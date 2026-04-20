<?php
declare(strict_types=1);

require_once __DIR__ . '/support/InMemoryApiKeyRepository.php';

final class ApiKeyService
{
    public function __construct(private InMemoryApiKeyRepository $repository) {}

    public function getApiKey(int $keyId): ?array
    {
        $key = $this->repository->findById($keyId);
        $this->repository->incrementUsageCount($keyId);
        $this->repository->updateLastUsedAt($keyId, time());

        return $key;
    }

    public function isApiKeyValid(int $keyId): bool
    {
        $key = $this->repository->findById($keyId);
        if ($key === null) {
            return false;
        }
        $this->repository->incrementValidationCount($keyId);

        return $key['expires_at'] > time();
    }
}

$repository = new InMemoryApiKeyRepository([
    1 => ['id' => 1, 'token' => 'abc', 'expires_at' => time() + 3600],
]);

$service = new ApiKeyService($repository);

$service->isApiKeyValid(1);
$service->isApiKeyValid(1);
var_export($service->getApiKey(1));
echo "\n";

echo "usage_count:      " . $repository->usageCount(1)      . "\n";
echo "validation_count: " . $repository->validationCount(1) . "\n";
