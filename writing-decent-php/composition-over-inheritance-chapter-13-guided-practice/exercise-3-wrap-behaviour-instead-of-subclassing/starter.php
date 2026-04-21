<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * `LoggingMailer extends SmtpMailer` overrides every method just to
 * sandwich `parent::*()` between two log lines. Three problems:
 *
 *   1. It is locked to one transport. There is no way to log a
 *      `SesMailer`, `SendgridMailer`, or `RecordingMailer` without
 *      writing `LoggingSesMailer extends SesMailer` and so on for
 *      every transport — a quadratic explosion.
 *
 *   2. Adding a method to `SmtpMailer` (e.g. `sendDelayed()`) silently
 *      bypasses logging until somebody remembers to override it here.
 *
 *   3. Anything that takes a `SmtpMailer` parameter cannot accept the
 *      logging variant — the signature is "the concrete class", not
 *      "anything that can send mail".
 */

class SmtpMailer
{
    public function __construct(public string $host) {}

    public function send(Email $email): void
    {
        TransportRecorder::$sent[] = "smtp({$this->host}) -> {$email->to}: {$email->subject}";
    }

    /** @param list<Email> $emails */
    public function sendBulk(array $emails): void
    {
        foreach ($emails as $email) {
            $this->send($email);
        }
    }
}

final class LoggingMailer extends SmtpMailer
{
    /** @var list<string> */
    public array $log = [];

    public function send(Email $email): void
    {
        $this->log[] = "before: {$email->to}";
        parent::send($email);
        $this->log[] = "after: {$email->to}";
    }

    /** @param list<Email> $emails */
    public function sendBulk(array $emails): void
    {
        $this->log[] = "before-bulk: " . count($emails);
        parent::sendBulk($emails);
        $this->log[] = "after-bulk: " . count($emails);
    }
}

/* ---------- driver ---------- */

TransportRecorder::reset();

$mailer = new LoggingMailer(host: 'mail.example.com');

$mailer->send(new Email(to: 'alice@example.com', subject: 'Hi'));
$mailer->sendBulk([
    new Email(to: 'bob@example.com',    subject: 'Bulk #1'),
    new Email(to: 'carol@example.com',  subject: 'Bulk #2'),
]);

echo "transport: " . json_encode(TransportRecorder::$sent) . "\n";
echo "log:       " . json_encode($mailer->log) . "\n";
echo "(notice: this only logs SmtpMailer; no way to log a SesMailer without LoggingSesMailer extends SesMailer)\n";
