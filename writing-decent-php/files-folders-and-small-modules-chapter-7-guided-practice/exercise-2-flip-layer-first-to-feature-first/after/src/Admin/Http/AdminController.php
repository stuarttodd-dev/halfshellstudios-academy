<?php
declare(strict_types=1);

namespace App\Admin\Http;

use App\Admin\User;

final class AdminController
{
    /** @param list<User> $users */
    public function __construct(private array $users) {}

    public function adminCount(): int
    {
        return count(array_filter($this->users, static fn (User $u): bool => $u->isAdmin));
    }
}
