<?php
declare(strict_types=1);

require_once __DIR__ . '/support/UnknownCarrierException.php';

final class ShippingService
{
    public function rateFor(string $carrier, int $weightInGrams, string $countryCode): int
    {
        if ($carrier === 'royal_mail') {
            if ($countryCode === 'GB') {
                if ($weightInGrams <= 100) {
                    return 165;
                } elseif ($weightInGrams <= 250) {
                    return 230;
                } elseif ($weightInGrams <= 1000) {
                    return 360;
                } else {
                    return 750;
                }
            } else {
                return 1200;
            }
        } elseif ($carrier === 'dpd') {
            return $countryCode === 'GB' ? 800 : 1800;
        } elseif ($carrier === 'fedex') {
            return $countryCode === 'GB' ? 1500 : 2500;
        } elseif ($carrier === 'click_and_collect') {
            return 0;
        } else {
            throw new UnknownCarrierException($carrier);
        }
    }
}

$service = new ShippingService();

$cases = [
    ['royal_mail',        50,    'GB'],
    ['royal_mail',        200,   'GB'],
    ['royal_mail',        900,   'GB'],
    ['royal_mail',        2000,  'GB'],
    ['royal_mail',        50,    'FR'],
    ['dpd',               500,   'GB'],
    ['dpd',               500,   'FR'],
    ['fedex',             500,   'GB'],
    ['fedex',             500,   'US'],
    ['click_and_collect', 0,     'GB'],
];

foreach ($cases as [$carrier, $grams, $country]) {
    printf("%-18s %5dg %s -> %d\n", $carrier, $grams, $country, $service->rateFor($carrier, $grams, $country));
}

try {
    $service->rateFor('hermes', 100, 'GB');
} catch (UnknownCarrierException $e) {
    echo "threw: " . $e->getMessage() . "\n";
}
