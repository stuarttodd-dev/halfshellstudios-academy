<?php
declare(strict_types=1);

require_once __DIR__ . '/support/UnknownCarrierException.php';

final class ShippingService
{
    public function rateFor(string $carrier, int $weightInGrams, string $countryCode): int
    {
        return match ($carrier) {
            'royal_mail'        => $this->royalMailRateFor($weightInGrams, $countryCode),
            'dpd'               => $countryCode === 'GB' ? 800  : 1800,
            'fedex'             => $countryCode === 'GB' ? 1500 : 2500,
            'click_and_collect' => 0,
            default             => throw new UnknownCarrierException($carrier),
        };
    }

    private function royalMailRateFor(int $weightInGrams, string $countryCode): int
    {
        if ($countryCode !== 'GB') {
            return 1200;
        }

        return match (true) {
            $weightInGrams <= 100  => 165,
            $weightInGrams <= 250  => 230,
            $weightInGrams <= 1000 => 360,
            default                => 750,
        };
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
