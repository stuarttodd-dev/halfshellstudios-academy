<?php
declare(strict_types=1);

final class User
{
    public function __construct(
        public readonly int    $id,
        public readonly string $email,
        private readonly bool  $active,
        private readonly bool  $emailVerified,
    ) {
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function emailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function is(?User $other): bool
    {
        return $other !== null && $other->id === $this->id;
    }
}

final class Comment
{
    public function __construct(
        public readonly int  $id,
        public readonly User $author,
    ) {
    }
}

final class Thread
{
    public function __construct(
        public readonly int    $id,
        public readonly string $title,
        private readonly bool  $open,
    ) {
    }

    public function isOpen(): bool
    {
        return $this->open;
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

final class StubCommentEmailRenderer
{
    public function renderCommentEmail(Thread $thread, Comment $comment): string
    {
        return "Comment #{$comment->id} on thread #{$thread->id}";
    }
}

final class RecordingAuditLog
{
    /** @var list<array{event:string, context:array<string, mixed>}> */
    public array $records = [];

    public function record(string $event, array $context = []): void
    {
        $this->records[] = ['event' => $event, 'context' => $context];
    }
}
