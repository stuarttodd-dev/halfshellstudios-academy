<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class ThreadCommentNotifier
{
    public function __construct(
        public RecordingMailer            $mailer,
        public StubCommentEmailRenderer   $renderer,
        public RecordingAuditLog          $audit,
    ) {
    }

    public function notifyOnThreadComment(?User $user, Thread $thread, Comment $comment): void
    {
        if ($user === null)                  { return; }
        if (! $user->isActive())             { return; }
        if (! $user->emailVerified())        { return; }
        if (! $thread->isOpen())             { return; }
        if ($comment->author->is($user))     { return; }

        // Future preconditions land here as one-liners, e.g.:
        // if ($user->hasMuted($thread))                            { return; }
        // if ($this->recentlyNotified($user, $comment, '1 hour'))  { return; }

        $this->mailer->send(
            $user->email,
            'New comment on ' . $thread->title,
            $this->renderer->renderCommentEmail($thread, $comment),
        );

        $this->audit->record('notification_sent', [
            'user'    => $user->id,
            'thread'  => $thread->id,
            'comment' => $comment->id,
        ]);
    }
}

require __DIR__ . '/support/scenarios.php';
runScenarios(fn () => new ThreadCommentNotifier(new RecordingMailer(), new StubCommentEmailRenderer(), new RecordingAuditLog()));
