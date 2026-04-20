<?php
declare(strict_types=1);

final class InMemoryDb
{
    private int $nextId = 0;

    /** @var array<string, list<array<string, mixed>>> */
    public array $tables = [];

    public function insert(string $table, array $row): int
    {
        $row['id']             = ++$this->nextId;
        $this->tables[$table][] = $row;

        return $row['id'];
    }
}

final class RecordingMailer
{
    /** @var list<array{to:string, subject:string, body:string}> */
    public array $sent = [];

    public function send(string $to, string $subject, string $body): void
    {
        $this->sent[] = ['to' => $to, 'subject' => $subject, 'body' => $body];
    }
}

final class RecordingLogger
{
    /** @var list<array{level:string, event:string, context:array<string, mixed>}> */
    public array $records = [];

    public function info(string $event, array $context = []): void
    {
        $this->records[] = ['level' => 'info', 'event' => $event, 'context' => $context];
    }

    public function warning(string $event, array $context = []): void
    {
        $this->records[] = ['level' => 'warning', 'event' => $event, 'context' => $context];
    }
}
