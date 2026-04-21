<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Decorator: same interface, wraps any other implementation.
 *
 *   - `Mailer` is the contract.
 *   - `SmtpMailer`, `SesMailer`, `RecordingMailer` are transports —
 *     siblings, not a parent and a child.
 *   - `LoggingMailer` *implements* `Mailer` and *takes* a `Mailer` in
 *     its constructor. It can wrap any of them.
 *
 * What this buys:
 *   1. One logger works for every transport — no quadratic explosion.
 *   2. Adding a method to the contract is enforced by the compiler:
 *      `LoggingMailer` will not satisfy `Mailer` until you implement it.
 *   3. Decorators stack. `RetryingMailer(LoggingMailer(SmtpMailer(...)))`
 *      reads top-to-bottom and you can disable any layer by swapping
 *      it for the inner one.
 */

interface Mailer
{
    public function send(Email $email): void;

    /** @param list<Email> $emails */
    public function sendBulk(array $emails): void;
}

final class SmtpMailer implements Mailer
{
    public function __construct(private string $host) {}

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

final class LoggingMailer implements Mailer
{
    /** @var list<string> */
    public array $log = [];

    public function __construct(private Mailer $inner) {}

    public function send(Email $email): void
    {
        $this->log[] = "before: {$email->to}";
        $this->inner->send($email);
        $this->log[] = "after: {$email->to}";
    }

    /**
     * Decorator design choice: log every individual send INSIDE the
     * bulk too (matches the starter's behaviour). We do this by
     * iterating ourselves and calling `$this->send` per email rather
     * than delegating to `$this->inner->sendBulk` — otherwise the
     * inner transport's bulk implementation runs unwrapped and we
     * lose the per-item visibility.
     *
     * The other valid choice would be `$this->inner->sendBulk($emails)`
     * — appropriate when bulk is materially cheaper than N singles
     * (e.g. one SMTP connection for the batch). Decorators always
     * have to decide: wrap *the call* or wrap *each effect inside it*.
     *
     * @param list<Email> $emails
     */
    public function sendBulk(array $emails): void
    {
        $this->log[] = "before-bulk: " . count($emails);

        foreach ($emails as $email) {
            $this->send($email);
        }

        $this->log[] = "after-bulk: " . count($emails);
    }
}

/* ---------- driver (identical observable output to starter.php) ---------- */

TransportRecorder::reset();

$mailer = new LoggingMailer(new SmtpMailer(host: 'mail.example.com'));

$mailer->send(new Email(to: 'alice@example.com', subject: 'Hi'));
$mailer->sendBulk([
    new Email(to: 'bob@example.com',   subject: 'Bulk #1'),
    new Email(to: 'carol@example.com', subject: 'Bulk #2'),
]);

echo "transport: " . json_encode(TransportRecorder::$sent) . "\n";
echo "log:       " . json_encode($mailer->log) . "\n";
echo "(notice: LoggingMailer would wrap any Mailer — SES, Sendgrid, an in-memory test double, anything)\n";
