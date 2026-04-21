<?php
declare(strict_types=1);

namespace DecentPhp\Ch7\Ex1\Money;

final class MoneyFormatter
{
    public static function gbp(int $pence): string
    {
        return '£' . number_format($pence / 100, 2);
    }
}
