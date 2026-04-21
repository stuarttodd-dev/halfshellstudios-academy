<?php
declare(strict_types=1);

require_once __DIR__ . '/support/sources.php';

function summariseDelivery(array $address): string
{
    $line = strtoupper(trim($address['line1'] ?? '')) . ', '
        . strtoupper(trim($address['postcode'] ?? ''));

    if (in_array($address['country'] ?? '', ['GB', 'IE'], true)) {
        $line .= ' (signature required)';
    }

    return $line;
}

function isUkAddress(array $address): bool
{
    return ($address['country'] ?? '') === 'GB';
}

function shippingRateFor(array $address, int $weightInGrams): int
{
    if (($address['country'] ?? '') === 'GB') {
        return $weightInGrams <= 1000 ? 360 : 750;
    }

    return 1200;
}

$order    = new StubOrderRow(' 10 Downing St ', 'sw1a 2aa',     'GB');
$customer = new StubCustomerRow('1 Eiffel Way',  '75007',        'FR');
$request  = new StubFormRequest(['line1' => '5 O\'Connell St', 'postcode' => 'd01 r2px', 'country' => 'IE']);

$fromOrder    = ['line1' => $order->ship_line1,             'postcode' => $order->ship_postcode,             'country' => $order->ship_country];
$fromCustomer = ['line1' => $customer->billing_line1,        'postcode' => $customer->billing_postcode,        'country' => $customer->billing_country];
$fromForm     = ['line1' => $request->input('line1'),        'postcode' => strtoupper($request->input('postcode')), 'country' => $request->input('country')];

foreach (['order' => $fromOrder, 'customer' => $fromCustomer, 'form' => $fromForm] as $label => $address) {
    printf("%-9s summary: %s\n",       $label, summariseDelivery($address));
    printf("%-9s isUk:    %s\n",       $label, isUkAddress($address) ? 'true' : 'false');
    printf("%-9s rate:    %d\n",       $label, shippingRateFor($address, 800));
}
