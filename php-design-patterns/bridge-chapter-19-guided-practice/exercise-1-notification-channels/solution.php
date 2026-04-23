<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $deviceToken = '',
    ) {}
}

/* ---- Implementor side: Channel ---- */

interface Channel
{
    public function deliver(User $user, string $subject, string $body): void;
}

final class RecordingEmailChannel implements Channel
{
    /** @var list<array{string,string,string}> */
    public array $sent = [];
    public function deliver(User $user, string $subject, string $body): void
    {
        $this->sent[] = [$user->email, $subject, $body];
    }
}

final class RecordingSmsChannel implements Channel
{
    /** @var list<array{string,string}> */
    public array $sent = [];
    public function deliver(User $user, string $subject, string $body): void
    {
        $this->sent[] = [$user->phone, "{$subject}: {$body}"];
    }
}

final class RecordingPushChannel implements Channel
{
    /** @var list<array{string,string,string}> */
    public array $sent = [];
    public function deliver(User $user, string $subject, string $body): void
    {
        $this->sent[] = [$user->deviceToken, $subject, $body];
    }
}

/* ---- Abstraction side: Notification (holds a Channel by composition) ---- */

abstract class Notification
{
    public function __construct(protected readonly Channel $channel) {}

    /** Each notification subtype provides its content; channels know how to deliver. */
    final public function send(User $user): void
    {
        $this->channel->deliver($user, $this->subject(), $this->body($user));
    }

    abstract protected function subject(): string;
    abstract protected function body(User $user): string;
}

final class PasswordResetNotification extends Notification
{
    protected function subject(): string { return 'Password reset'; }
    protected function body(User $u): string { return "Hi {$u->id}, click the link to reset your password."; }
}

final class TwoFactorNotification extends Notification
{
    public function __construct(Channel $channel, private readonly string $code) { parent::__construct($channel); }
    protected function subject(): string { return 'Your code'; }
    protected function body(User $u): string { return "Your verification code is {$this->code}."; }
}

// ---- assertions -------------------------------------------------------------

$user = new User('u1', email: 'a@b.test', phone: '555', deviceToken: 'dev-1');

$emailCh = new RecordingEmailChannel();
$smsCh   = new RecordingSmsChannel();
$pushCh  = new RecordingPushChannel();

(new PasswordResetNotification($emailCh))->send($user);
(new PasswordResetNotification($smsCh))->send($user);
(new TwoFactorNotification($pushCh, code: '123456'))->send($user);

pdp_assert_eq([['a@b.test', 'Password reset', 'Hi u1, click the link to reset your password.']], $emailCh->sent, 'password reset via email');
pdp_assert_eq([['555', 'Password reset: Hi u1, click the link to reset your password.']], $smsCh->sent, 'password reset via sms');
pdp_assert_eq([['dev-1', 'Your code', 'Your verification code is 123456.']], $pushCh->sent, '2fa via push (new channel, no edits to existing notifications)');

// adding a notification: only one new class, works on every channel
final class WelcomeNotification extends Notification {
    protected function subject(): string { return 'Welcome'; }
    protected function body(User $u): string { return "Welcome, {$u->id}!"; }
}
(new WelcomeNotification($emailCh))->send($user);
pdp_assert_eq(2, count($emailCh->sent), 'new notification works on existing channel without changes');

pdp_done();
