<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

global $db, $mailer, $logger;
$db     = new InMemoryDb();
$mailer = new RecordingMailer();
$logger = new RecordingLogger();

function processFeedbackSubmission(array $input): array
{
    global $db, $mailer, $logger;

    $errors = [];
    if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'invalid_email';
    }
    if (empty($input['message']) || strlen($input['message']) < 10) {
        $errors[] = 'message_too_short';
    }
    if ($errors !== []) {
        $logger->warning('feedback_invalid', ['input' => $input, 'errors' => $errors]);
        return ['ok' => false, 'errors' => $errors];
    }

    $id = $db->insert('feedback', [
        'email'        => $input['email'],
        'message'      => $input['message'],
        'submitted_at' => time(),
    ]);

    $mailer->send('team@example.com', 'New feedback', "From {$input['email']}: {$input['message']}");

    $mailer->send($input['email'], 'Thanks for the feedback', 'We received your message.');

    $logger->info('feedback_recorded', ['id' => $id]);

    return ['ok' => true, 'id' => $id];
}

var_export(processFeedbackSubmission([
    'email'   => 'sam@example.com',
    'message' => 'I really enjoyed the course.',
]));
echo "\n";

var_export(processFeedbackSubmission([
    'email'   => 'not-an-email',
    'message' => 'short',
]));
echo "\n";

echo "mails: " . count($mailer->sent) . "\n";
echo "logs: "  . count($logger->records) . "\n";
