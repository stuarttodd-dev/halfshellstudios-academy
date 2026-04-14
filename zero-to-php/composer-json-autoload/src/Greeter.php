<?php

declare(strict_types=1);

namespace App;

final class Greeter
{
    public function __construct(
        private string $name = 'world',
    ) {
    }

    public function greet(): string
    {
        return sprintf('Hello, %s!', $this->name);
    }
}
