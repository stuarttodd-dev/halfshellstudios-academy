<?php
declare(strict_types=1);

namespace App\Services;

final class NotificationService
{
    /** @var list<string> */
    public static array $sent = [];

    public function send(string $message): void
    {
        self::$sent[] = $message;
    }
}
