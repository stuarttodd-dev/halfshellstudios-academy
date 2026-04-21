<?php
declare(strict_types=1);

namespace App\Admin;

final class User
{
    public function __construct(public readonly int $id, public readonly string $email, public readonly bool $isAdmin) {}
}
