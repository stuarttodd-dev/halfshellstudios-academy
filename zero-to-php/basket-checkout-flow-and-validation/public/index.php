<?php
declare(strict_types=1);

session_start();
$_SESSION['basket'] ??= [
    ['product_id' => 1, 'name' => 'T-Shirt', 'price_cents' => 1999, 'qty' => 1],
];

function validateCheckout(string $name, string $email, array $basket): array
{
    $errors = [];
    if (trim($name) === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is invalid.';
    }
    if ($basket === []) {
        $errors[] = 'Basket cannot be empty.';
    }
    return $errors;
}

$errors = [];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = (string) ($_POST['name'] ?? '');
    $email = (string) ($_POST['email'] ?? '');
    $errors = validateCheckout($name, $email, $_SESSION['basket']);
    if ($errors === []) {
        $_SESSION['basket'] = [];
        $message = 'Checkout complete.';
    }
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Basket Checkout</title></head>
<body>
<h1>Basket checkout flow and validation</h1>
<?php if ($message !== ''): ?><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?>
<form method="post">
    <label>Name <input name="name"></label>
    <label>Email <input name="email" type="email"></label>
    <button type="submit">Checkout</button>
</form>
</body>
</html>
