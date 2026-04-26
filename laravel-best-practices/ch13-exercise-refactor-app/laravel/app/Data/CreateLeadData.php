<?php

namespace App\Data;

readonly class CreateLeadData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $message,
    ) {}

    /**
     * @param  array{name:string,email:string,message:string}  $v
     */
    public static function fromValidated(array $v): self
    {
        return new self($v['name'], $v['email'], $v['message']);
    }
}
