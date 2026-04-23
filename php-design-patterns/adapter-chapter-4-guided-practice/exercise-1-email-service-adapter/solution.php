<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Domain interface — vocabulary chosen by us, not by the SDK. */
interface Mailer
{
    public function send(string $to, string $from, string $subject, string $htmlBody): void;
}

/** A stub of the foreign SDK we don't control: weird parameter names, weird result shape. */
final class ThirdPartyEmailClient
{
    /** @var list<array<string, mixed>> */
    public array $sent = [];
    public function __construct(public readonly string $apiKey) {}

    public function send_message(array $payload): array
    {
        $this->sent[] = $payload;
        if (($payload['to_addr'] ?? '') === 'fail@example.com') {
            return ['status_code' => 'ERR', 'err_text' => 'bad address'];
        }
        return ['status_code' => 'OK'];
    }
}

/** Adapter: implements OUR interface, translates inwards to the foreign SDK. */
final class ThirdPartyMailerAdapter implements Mailer
{
    public function __construct(private readonly ThirdPartyEmailClient $client) {}

    public function send(string $to, string $from, string $subject, string $htmlBody): void
    {
        $result = $this->client->send_message([
            'to_addr'     => $to,
            'from_addr'   => $from,
            'subj'        => $subject,
            'msg_html'    => $htmlBody,
            'tracking_id' => uniqid('email_', more_entropy: true),
        ]);
        if (($result['status_code'] ?? null) !== 'OK') {
            throw new \RuntimeException('Failed: ' . ($result['err_text'] ?? 'unknown'));
        }
    }
}

/** A recording test double of the *domain* interface — for tests of the caller. */
final class RecordingMailer implements Mailer
{
    /** @var list<array{to:string,from:string,subject:string,htmlBody:string}> */
    public array $sent = [];
    public function send(string $to, string $from, string $subject, string $htmlBody): void
    {
        $this->sent[] = compact('to', 'from', 'subject', 'htmlBody');
    }
}

/** Caller depends on the domain interface — no SDK vocabulary visible. */
final class WelcomeEmailer
{
    public function __construct(private readonly Mailer $mailer) {}
    public function send(object $user): void
    {
        $this->mailer->send($user->email, 'noreply@example.com', 'Welcome', '<p>Welcome aboard</p>');
    }
}

// ---- assertions -------------------------------------------------------------

// (1) Adapter translates correctly inwards (we test it against the foreign SDK).
$client  = new ThirdPartyEmailClient(apiKey: 'k');
$adapter = new ThirdPartyMailerAdapter($client);
$adapter->send('alice@example.com', 'noreply@example.com', 'Hi', '<p>Hi</p>');
pdp_assert_eq(1, count($client->sent), 'adapter forwarded one call to the SDK');
pdp_assert_eq('alice@example.com', $client->sent[0]['to_addr'], 'adapter mapped to->to_addr');
pdp_assert_eq('Hi',                $client->sent[0]['subj'],    'adapter mapped subject->subj');
pdp_assert_eq('<p>Hi</p>',         $client->sent[0]['msg_html'], 'adapter mapped htmlBody->msg_html');
pdp_assert_throws(\RuntimeException::class, fn () => $adapter->send('fail@example.com', 'x', 'x', 'x'), 'adapter raises on SDK error');

// (2) Caller is testable through the domain interface — no SDK in sight.
$mailer = new RecordingMailer();
(new WelcomeEmailer($mailer))->send((object) ['email' => 'bob@example.com']);
pdp_assert_eq(1, count($mailer->sent), 'WelcomeEmailer used the injected mailer once');
pdp_assert_eq('bob@example.com', $mailer->sent[0]['to'], 'WelcomeEmailer addressed the user');
pdp_assert_eq('Welcome',          $mailer->sent[0]['subject'], 'WelcomeEmailer used a Welcome subject');

pdp_done();
