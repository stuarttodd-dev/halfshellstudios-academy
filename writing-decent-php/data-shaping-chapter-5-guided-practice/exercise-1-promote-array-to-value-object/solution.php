<?php
declare(strict_types=1);

require_once __DIR__ . '/support/sources.php';

final class Address
{
    private const KNOWN_COUNTRIES         = ['GB', 'IE', 'FR', 'DE', 'US'];
    private const SIGNATURE_COUNTRIES     = ['GB', 'IE'];

    public function __construct(
        public readonly string $line1,
        public readonly string $postcode,
        public readonly string $country,
    ) {
        if (trim($line1) === '') {
            throw new InvalidArgumentException('Address line1 must not be empty.');
        }

        if (trim($postcode) === '') {
            throw new InvalidArgumentException('Address postcode must not be empty.');
        }

        if (! in_array($country, self::KNOWN_COUNTRIES, true)) {
            throw new InvalidArgumentException("Unknown country code: {$country}");
        }
    }

    public static function fromOrder(StubOrderRow $order): self
    {
        return new self(
            line1:    $order->ship_line1,
            postcode: $order->ship_postcode,
            country:  $order->ship_country,
        );
    }

    public static function fromCustomer(StubCustomerRow $customer): self
    {
        return new self(
            line1:    $customer->billing_line1,
            postcode: $customer->billing_postcode,
            country:  $customer->billing_country,
        );
    }

    public static function fromFormRequest(StubFormRequest $request): self
    {
        return new self(
            line1:    $request->input('line1'),
            postcode: strtoupper($request->input('postcode')),
            country:  $request->input('country'),
        );
    }

    public function isUk(): bool
    {
        return $this->country === 'GB';
    }

    public function requiresSignature(): bool
    {
        return in_array($this->country, self::SIGNATURE_COUNTRIES, true);
    }

    public function summary(): string
    {
        $line = strtoupper(trim($this->line1)) . ', ' . strtoupper(trim($this->postcode));

        if ($this->requiresSignature()) {
            $line .= ' (signature required)';
        }

        return $line;
    }
}

/**
 * Shipping rate intentionally stays as a free function: it depends on weight,
 * which is not a property of an address, so it does not belong on the value
 * object. Note how the function signature is now typed.
 */
function shippingRateFor(Address $address, int $weightInGrams): int
{
    if ($address->isUk()) {
        return $weightInGrams <= 1000 ? 360 : 750;
    }

    return 1200;
}

$order    = new StubOrderRow(' 10 Downing St ', 'sw1a 2aa', 'GB');
$customer = new StubCustomerRow('1 Eiffel Way',  '75007',    'FR');
$request  = new StubFormRequest(['line1' => '5 O\'Connell St', 'postcode' => 'd01 r2px', 'country' => 'IE']);

$addresses = [
    'order'    => Address::fromOrder($order),
    'customer' => Address::fromCustomer($customer),
    'form'     => Address::fromFormRequest($request),
];

foreach ($addresses as $label => $address) {
    printf("%-9s summary: %s\n", $label, $address->summary());
    printf("%-9s isUk:    %s\n", $label, $address->isUk() ? 'true' : 'false');
    printf("%-9s rate:    %d\n", $label, shippingRateFor($address, 800));
}
