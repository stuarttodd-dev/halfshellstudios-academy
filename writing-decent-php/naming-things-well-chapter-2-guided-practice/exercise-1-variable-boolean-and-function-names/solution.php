<?php
declare(strict_types=1);

const VAT_MULTIPLIER             = 1.2;
const PROMOTION_MIN_TOTAL_POUNDS = 100;
const ADULT_AGE_YEARS            = 18;
const CUSTOMER_STATUS_ACTIVE     = 'a';

function isOrderEligibleForPromotion(array $order, array $customer): bool
{
    $orderTotalIncludingVatPounds = $order['amt'] * VAT_MULTIPLIER;

    $isActiveAdultVipCustomer = $customer['vip']
        && $customer['stat'] === CUSTOMER_STATUS_ACTIVE
        && $customer['age'] >= ADULT_AGE_YEARS;

    $emailAddress      = $customer['eml'];
    $hasContactableEmail = $emailAddress !== ''
        && filter_var($emailAddress, FILTER_VALIDATE_EMAIL) !== false;

    return $orderTotalIncludingVatPounds > PROMOTION_MIN_TOTAL_POUNDS
        && $isActiveAdultVipCustomer
        && $hasContactableEmail;
}

var_export(isOrderEligibleForPromotion(
    ['amt' => 120],
    ['vip' => true, 'stat' => 'a', 'age' => 21, 'eml' => 'sam@example.com'],
));
echo "\n";

var_export(isOrderEligibleForPromotion(
    ['amt' => 50],
    ['vip' => true, 'stat' => 'a', 'age' => 21, 'eml' => 'sam@example.com'],
));
echo "\n";
