<?php
declare(strict_types=1);

function validateCheckout(string $name, string $email, array $basket): array {
    $errors = [];

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email is invalid.';
    }
    if ($basket === []) {
        $errors['basket'] = 'Basket cannot be empty.';
    }

    return $errors;
}

$basket = [['product_id' => 10, 'name' => 'Mug', 'price_cents' => 1299, 'qty' => 1]];
$errors = validateCheckout('Stu', 'stu@example.com', $basket);

echo $errors === [] ? "checkout_ok\n" : "checkout_error\n";
