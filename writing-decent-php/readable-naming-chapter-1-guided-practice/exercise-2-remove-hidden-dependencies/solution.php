<?php
declare(strict_types=1);

function canSendDigest(array $user, bool $mailEnabled, int $currentHour): bool
{
    if (! $mailEnabled) {
        return false;
    }

    if (($user['email'] ?? '') === '') {
        return false;
    }

    return $currentHour >= 9;
}

$_ENV['MAIL_ENABLED'] = '1';

$mailEnabled = (($_ENV['MAIL_ENABLED'] ?? '0') === '1');
$currentHour = 9;

var_export(canSendDigest(['email' => 'sam@example.com'], $mailEnabled, $currentHour));
echo "\n";
var_export(canSendDigest(['email' => ''],                $mailEnabled, $currentHour));
echo "\n";
var_export(canSendDigest(['email' => 'sam@example.com'], false,        $currentHour));
echo "\n";
var_export(canSendDigest(['email' => 'sam@example.com'], $mailEnabled, 8));
echo "\n";
