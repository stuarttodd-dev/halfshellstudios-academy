<?php
declare(strict_types=1);

require_once __DIR__ . '/support/Customer.php';

function billingAddressLineFor(?Customer $customer): ?string
{
    if ($customer === null) {
        return null;
    }

    if (! $customer->isActive()) {
        return null;
    }

    $billingAddress = $customer->billingAddress;
    if ($billingAddress === null) {
        return null;
    }

    if ($billingAddress->line1 === '' || $billingAddress->postcode === '') {
        return null;
    }

    return strtoupper(trim($billingAddress->line1))
        . ', '
        . strtoupper(trim($billingAddress->postcode));
}

$cases = [
    'all good'           => new Customer(true,  new BillingAddress(' 10 Downing St ', 'sw1a 2aa')),
    'inactive'           => new Customer(false, new BillingAddress('10 Downing St',   'SW1A 2AA')),
    'no billing address' => new Customer(true,  null),
    'empty line1'        => new Customer(true,  new BillingAddress('',                'SW1A 2AA')),
    'empty postcode'     => new Customer(true,  new BillingAddress('10 Downing St',   '')),
    'null customer'      => null,
];

foreach ($cases as $label => $customer) {
    echo str_pad($label, 20) . ' => ' . var_export(billingAddressLineFor($customer), true) . "\n";
}
