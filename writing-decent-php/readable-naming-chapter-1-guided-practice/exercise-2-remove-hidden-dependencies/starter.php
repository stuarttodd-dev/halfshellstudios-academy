<?php
declare(strict_types=1);

$_ENV['MAIL_ENABLED'] = '1';

function canSendDigest(array $user): bool
{
    global $currentHour;
    $currentHour = $currentHour ?? 9;

    if (($_ENV['MAIL_ENABLED'] ?? '0') !== '1') {
        return false;
    }

    if (($user['email'] ?? '') === '') {
        return false;
    }

    return $currentHour >= 9;
}

var_export(canSendDigest(['email' => 'sam@example.com']));
