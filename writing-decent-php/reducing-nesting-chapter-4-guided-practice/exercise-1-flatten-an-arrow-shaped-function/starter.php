<?php
declare(strict_types=1);

require_once __DIR__ . '/support/Customer.php';

function billingAddressLineFor(?Customer $customer): ?string
{
    if ($customer !== null) {
        if ($customer->isActive()) {
            if ($customer->billingAddress !== null) {
                if ($customer->billingAddress->line1 !== '') {
                    if ($customer->billingAddress->postcode !== '') {
                        return strtoupper(trim($customer->billingAddress->line1))
                            . ', '
                            . strtoupper(trim($customer->billingAddress->postcode));
                    }

                    return null;
                }

                return null;
            }

            return null;
        }

        return null;
    }

    return null;
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
