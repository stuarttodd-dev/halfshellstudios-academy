<?php
declare(strict_types=1);

/**
 * @param callable():object $makeNotifier  Returns a fresh notifier with public
 *                                          $mailer and $audit recording stubs.
 */
function runScenarios(callable $makeNotifier): void
{
    $author = new User(id: 1, email: 'author@example.com', active: true,  emailVerified: true);
    $reader = new User(id: 2, email: 'reader@example.com', active: true,  emailVerified: true);

    $openThread   = new Thread(id: 10, title: 'Decent PHP', open: true);
    $closedThread = new Thread(id: 11, title: 'Old thread', open: false);

    $comment = new Comment(id: 100, author: $author);

    $scenarios = [
        'happy path'        => [$reader, $openThread,   $comment],
        'inactive user'     => [new User(2, 'r@example.com', false, true), $openThread, $comment],
        'unverified email'  => [new User(2, 'r@example.com', true, false), $openThread, $comment],
        'closed thread'     => [$reader, $closedThread, $comment],
        'author is reader'  => [$author, $openThread,   $comment],
        'null user'         => [null,    $openThread,   $comment],
    ];

    foreach ($scenarios as $label => [$user, $thread, $comment]) {
        $notifier = $makeNotifier();
        $notifier->notifyOnThreadComment($user, $thread, $comment);
        printf(
            "%-18s mails=%d audits=%d\n",
            $label,
            count($notifier->mailer->sent),
            count($notifier->audit->records),
        );
    }
}
