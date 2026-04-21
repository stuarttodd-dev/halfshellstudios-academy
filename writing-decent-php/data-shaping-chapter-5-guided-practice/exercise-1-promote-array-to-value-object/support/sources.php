<?php
declare(strict_types=1);

/**
 * Tiny stand-ins for the database/HTTP shapes referenced in the lesson
 * snippet so both the starter and the solution can run end-to-end.
 */

final class StubOrderRow
{
    public function __construct(
        public readonly string $ship_line1,
        public readonly string $ship_postcode,
        public readonly string $ship_country,
    ) {
    }
}

final class StubCustomerRow
{
    public function __construct(
        public readonly string $billing_line1,
        public readonly string $billing_postcode,
        public readonly string $billing_country,
    ) {
    }
}

final class StubFormRequest
{
    /** @param array<string, string> $input */
    public function __construct(private array $input) {}

    public function input(string $key): string
    {
        return $this->input[$key] ?? '';
    }
}
