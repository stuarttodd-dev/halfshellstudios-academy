<?php
declare(strict_types=1);

interface UserSelectionStrategy
{
    public function include(array $user): bool;
}

final class ActiveUserSelectionStrategy implements UserSelectionStrategy
{
    public function include(array $user): bool
    {
        return ($user['active'] ?? false) === true;
    }
}

final class UserExportPipeline
{
    public function __construct(private UserSelectionStrategy $strategy) {}

    public function run(array $users): array
    {
        $emails = [];

        foreach ($users as $user) {
            if (! $this->strategy->include($user)) {
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
}

$pipeline = new UserExportPipeline(new ActiveUserSelectionStrategy());
var_export($pipeline->run([
    ['active' => true,  'email' => 'a@example.com'],
    ['active' => false, 'email' => 'b@example.com'],
    ['active' => true,  'email' => ''],
]));
