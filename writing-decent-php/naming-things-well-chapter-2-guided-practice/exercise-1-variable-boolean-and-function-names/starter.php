<?php
declare(strict_types=1);

function process(array $o, array $c): bool
{
    $t = $o['amt'] * 1.2;
    $f = $c['vip'] && $c['stat'] === 'a' && $c['age'] >= 18;
    $e = $c['eml'] !== '' && filter_var($c['eml'], FILTER_VALIDATE_EMAIL);

    if ($t > 100 && $f && $e) {
        return true;
    }

    return false;
}

var_export(process(
    ['amt' => 120],
    ['vip' => true, 'stat' => 'a', 'age' => 21, 'eml' => 'sam@example.com'],
));
echo "\n";

var_export(process(
    ['amt' => 50],
    ['vip' => true, 'stat' => 'a', 'age' => 21, 'eml' => 'sam@example.com'],
));
echo "\n";
