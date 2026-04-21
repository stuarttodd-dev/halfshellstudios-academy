<?php
declare(strict_types=1);

final class Email
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
    ) {}
}

/** Records every "send" so we can assert against it deterministically. */
final class TransportRecorder
{
    /** @var list<string> */
    public static array $sent = [];

    public static function reset(): void
    {
        self::$sent = [];
    }
}
