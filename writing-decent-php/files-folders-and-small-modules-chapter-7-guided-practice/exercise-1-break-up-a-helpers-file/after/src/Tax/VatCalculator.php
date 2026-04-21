<?php
declare(strict_types=1);

namespace DecentPhp\Ch7\Ex1\Tax;

final class VatCalculator
{
    public static function calculate(int $netInPence, string $country): int
    {
        return match ($country) {
            'GB', 'IE' => (int) round($netInPence * 0.20),
            'DE'       => (int) round($netInPence * 0.19),
            'FR'       => (int) round($netInPence * 0.20),
            default    => 0,
        };
    }
}
