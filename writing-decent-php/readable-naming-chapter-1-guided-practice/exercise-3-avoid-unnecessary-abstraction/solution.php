<?php
declare(strict_types=1);

function activeEmails(array $users): array
{
    $emails = [];

    foreach ($users as $user) {
        if (($user['active'] ?? false) !== true) {
            continue;
        }

        $email = (string) ($user['email'] ?? '');
        if ($email === '') {
            continue;
        }

        $emails[] = $email;
    }

    return $emails;
}

var_export(activeEmails([
    ['active' => true,  'email' => 'a@example.com'],
    ['active' => false, 'email' => 'b@example.com'],
    ['active' => true,  'email' => ''],
]));
echo "\n";
