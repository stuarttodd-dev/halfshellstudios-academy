<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class FeedbackSubmissionHandler
{
    private const MIN_MESSAGE_LENGTH = 10;

    public function __construct(
        private InMemoryDb       $database,
        private RecordingMailer  $mailer,
        private RecordingLogger  $logger,
        private string           $teamRecipient = 'team@example.com',
    ) {
    }

    /**
     * @param  array<string, mixed> $input
     * @return array{ok:true, id:int} | array{ok:false, errors:list<string>}
     */
    public function handle(array $input): array
    {
        $validationErrors = $this->validate($input);
        if ($validationErrors !== []) {
            $this->logger->warning('feedback_invalid', ['input' => $input, 'errors' => $validationErrors]);

            return ['ok' => false, 'errors' => $validationErrors];
        }

        $feedbackId = $this->store($input);

        $this->notifyTeam($input);
        $this->thankSubmitter($input['email']);

        $this->logger->info('feedback_recorded', ['id' => $feedbackId]);

        return ['ok' => true, 'id' => $feedbackId];
    }

    /** @return list<string> */
    private function validate(array $input): array
    {
        $errors = [];

        if (empty($input['email']) || ! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'invalid_email';
        }

        if (empty($input['message']) || strlen($input['message']) < self::MIN_MESSAGE_LENGTH) {
            $errors[] = 'message_too_short';
        }

        return $errors;
    }

    private function store(array $input): int
    {
        return $this->database->insert('feedback', [
            'email'        => $input['email'],
            'message'      => $input['message'],
            'submitted_at' => time(),
        ]);
    }

    private function notifyTeam(array $input): void
    {
        $this->mailer->send(
            $this->teamRecipient,
            'New feedback',
            "From {$input['email']}: {$input['message']}",
        );
    }

    private function thankSubmitter(string $submitterEmail): void
    {
        $this->mailer->send(
            $submitterEmail,
            'Thanks for the feedback',
            'We received your message.',
        );
    }
}

$database = new InMemoryDb();
$mailer   = new RecordingMailer();
$logger   = new RecordingLogger();

$handler = new FeedbackSubmissionHandler($database, $mailer, $logger);

var_export($handler->handle([
    'email'   => 'sam@example.com',
    'message' => 'I really enjoyed the course.',
]));
echo "\n";

var_export($handler->handle([
    'email'   => 'not-an-email',
    'message' => 'short',
]));
echo "\n";

echo "mails: " . count($mailer->sent) . "\n";
echo "logs: "  . count($logger->records) . "\n";
