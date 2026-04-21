<?php
declare(strict_types=1);

namespace DecentPhp\Ch7\Ex1\Audit;

use AuditStore;

final class AuditLog
{
    /** @param array<string, mixed> $context */
    public function record(string $message, array $context = []): void
    {
        AuditStore::append($message, $context);
    }
}
