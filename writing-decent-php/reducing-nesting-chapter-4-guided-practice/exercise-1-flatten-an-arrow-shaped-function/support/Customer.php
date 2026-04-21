<?php
declare(strict_types=1);

final class BillingAddress
{
    public function __construct(
        public readonly string $line1,
        public readonly string $postcode,
    ) {
    }
}

final class Customer
{
    public function __construct(
        private readonly bool             $active,
        public readonly ?BillingAddress   $billingAddress,
    ) {
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
