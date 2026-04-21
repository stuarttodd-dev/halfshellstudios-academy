<?php
declare(strict_types=1);

namespace DecentPhp\Ch7\Ex1\Access;

use Db;

final class AdminCheck
{
    public function isAdmin(int $userId): bool
    {
        return Db::fetchValue("SELECT is_admin FROM users WHERE id = {$userId}") === true;
    }
}
