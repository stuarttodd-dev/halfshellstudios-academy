<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class User
{
    public function __construct(
        public readonly string $email,
        public readonly string $phone,
    ) {}
}

final class RecordingLogger
{
    /** @var list<string> */
    public array $log = [];
    public function info(string $message): void { $this->log[] = $message; }
}

abstract class Notifier
{
    public function __construct(protected readonly RecordingLogger $logger) {}

    /** Workflow shape — locked for all subclasses. */
    final public function send(User $user, string $message): void
    {
        $this->logger->info("sending {$this->channelName()}");
        $this->deliver($user, $message);
        $this->logger->info("{$this->channelName()} sent");
    }

    abstract protected function channelName(): string;
    abstract protected function deliver(User $user, string $message): void;
}

interface Mailer { public function send(string $to, string $message): void; }
interface SmsGateway { public function send(string $phone, string $message): void; }

final class RecordingMailer implements Mailer
{
    /** @var list<array{string,string}> */
    public array $sent = [];
    public function send(string $to, string $message): void { $this->sent[] = [$to, $message]; }
}
final class RecordingSms implements SmsGateway
{
    /** @var list<array{string,string}> */
    public array $sent = [];
    public function send(string $phone, string $message): void { $this->sent[] = [$phone, $message]; }
}

final class EmailNotifier extends Notifier
{
    public function __construct(RecordingLogger $logger, private readonly Mailer $mailer)
    {
        parent::__construct($logger);
    }
    protected function channelName(): string { return 'email'; }
    protected function deliver(User $user, string $message): void
    {
        $this->mailer->send($user->email, $message);
    }
}

final class SmsNotifier extends Notifier
{
    public function __construct(RecordingLogger $logger, private readonly SmsGateway $sms)
    {
        parent::__construct($logger);
    }
    protected function channelName(): string { return 'sms'; }
    protected function deliver(User $user, string $message): void
    {
        $this->sms->send($user->phone, $message);
    }
}

// ---- assertions -------------------------------------------------------------

$user = new User(email: 'a@b.test', phone: '555');

$logger = new RecordingLogger();
$mailer = new RecordingMailer();
$emailer = new EmailNotifier($logger, $mailer);
$emailer->send($user, 'hi');
pdp_assert_eq(['sending email', 'email sent'], $logger->log, 'email workflow logs around delivery');
pdp_assert_eq([['a@b.test', 'hi']], $mailer->sent, 'email actually delivered');

$logger = new RecordingLogger();
$sms = new RecordingSms();
$smser = new SmsNotifier($logger, $sms);
$smser->send($user, 'hi');
pdp_assert_eq(['sending sms', 'sms sent'], $logger->log, 'sms workflow logs around delivery');
pdp_assert_eq([['555', 'hi']], $sms->sent, 'sms actually delivered');

// base workflow tested via an anonymous subclass — no real channel needed
$logger = new RecordingLogger();
$delivered = [];
$test = new class($logger, $delivered) extends Notifier {
    public function __construct(RecordingLogger $logger, public array &$delivered)
    {
        parent::__construct($logger);
    }
    protected function channelName(): string { return 'fake'; }
    protected function deliver(User $user, string $message): void
    {
        $this->delivered[] = "{$user->email}:{$message}";
    }
};
$test->send($user, 'hi');
pdp_assert_eq(['sending fake', 'fake sent'], $logger->log, 'workflow runs in order regardless of subclass');
pdp_assert_eq(['a@b.test:hi'], $delivered, 'anonymous subclass deliver() called');

pdp_done();
