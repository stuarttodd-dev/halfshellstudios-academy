<?php
declare(strict_types=1);

/**
 * Tiny in-memory stand-ins so both before/run.php and after/run.php
 * exercise the same dependencies and produce identical output.
 */

final class Mailer
{
    /** @var list<array{to: string, subject: string}> */
    public static array $sent = [];

    public static function reset(): void
    {
        self::$sent = [];
    }

    public static function send(string $to, string $subject): void
    {
        self::$sent[] = ['to' => $to, 'subject' => $subject];
    }
}

final class Db
{
    /** @var array<int, bool> */
    private static array $isAdminByUserId = [
        1  => true,
        2  => false,
        99 => true,
    ];

    public static function fetchValue(string $sql): mixed
    {
        if (preg_match('/SELECT is_admin FROM users WHERE id = (\d+)/', $sql, $m)) {
            return self::$isAdminByUserId[(int) $m[1]] ?? false;
        }

        throw new RuntimeException("unsupported sql: {$sql}");
    }
}

final class AuditStore
{
    /** @var list<array{message: string, context: array<string, mixed>}> */
    public static array $entries = [];

    public static function reset(): void
    {
        self::$entries = [];
    }

    public static function append(string $message, array $context = []): void
    {
        self::$entries[] = ['message' => $message, 'context' => $context];
    }
}
