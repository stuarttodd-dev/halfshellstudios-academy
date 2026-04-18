<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['basket']) || !is_array($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

$products = [
    1 => ['id' => 1, 'name' => 'T-Shirt', 'price_cents' => 1999],
    2 => ['id' => 2, 'name' => 'Mug', 'price_cents' => 1299],
    3 => ['id' => 3, 'name' => 'Sticker Pack', 'price_cents' => 499],
];

function money(int $cents): string
{
    return '$' . number_format($cents / 100, 2);
}

function basketTotals(array $basket): array
{
    $itemCount = 0;
    $subtotalCents = 0;

    foreach ($basket as $item) {
        $qty = (int) $item['qty'];
        $itemCount += $qty;
        $subtotalCents += ((int) $item['price_cents']) * $qty;
    }

    return ['item_count' => $itemCount, 'subtotal_cents' => $subtotalCents];
}

function addToBasket(array &$basket, array $product): void
{
    foreach ($basket as &$item) {
        if ((int) $item['product_id'] === (int) $product['id']) {
            $item['qty']++;
            return;
        }
    }

    $basket[] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price_cents' => $product['price_cents'],
        'qty' => 1,
    ];
}

function updateQuantity(array &$basket, int $productId, int $qty): bool
{
    if ($qty < 1) {
        return false;
    }

    foreach ($basket as &$item) {
        if ((int) $item['product_id'] === $productId) {
            $item['qty'] = $qty;
            return true;
        }
    }

    return false;
}

function removeItem(array &$basket, int $productId): void
{
    $basket = array_values(array_filter(
        $basket,
        static fn(array $item): bool => (int) $item['product_id'] !== $productId
    ));
}

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

$flash = '';
$checkoutErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        if (isset($products[$productId])) {
            addToBasket($_SESSION['basket'], $products[$productId]);
            $flash = 'Item added to basket.';
        }
    } elseif ($action === 'update') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $qty = (int) ($_POST['qty'] ?? 0);
        if (updateQuantity($_SESSION['basket'], $productId, $qty)) {
            $flash = 'Quantity updated.';
        } else {
            $flash = 'Quantity must be at least 1.';
        }
    } elseif ($action === 'remove') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        removeItem($_SESSION['basket'], $productId);
        $flash = 'Item removed.';
    } elseif ($action === 'checkout') {
        $name = (string) ($_POST['name'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $checkoutErrors = validateCheckout($name, $email, $_SESSION['basket']);

        if ($checkoutErrors === []) {
            $_SESSION['basket'] = [];
            $flash = 'Checkout complete. Basket cleared.';
        }
    }
}

$basket = $_SESSION['basket'];
$totals = basketTotals($basket);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Basket Capstone</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 24px auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .flash { background: #eef8ee; border: 1px solid #9fd39f; padding: 10px; margin: 12px 0; }
        .error { background: #fdeaea; border: 1px solid #e39b9b; padding: 10px; margin: 12px 0; }
        form.inline { display: inline; }
    </style>
</head>
<body>
<h1>Basket Capstone</h1>

<?php if ($flash !== ''): ?>
    <div class="flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($checkoutErrors !== []): ?>
    <div class="error">
        <?php foreach ($checkoutErrors as $error): ?>
            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2>Products</h2>
<?php foreach ($products as $product): ?>
    <form method="post" class="inline">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <button type="submit">Add <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?> (<?= money((int) $product['price_cents']) ?>)</button>
    </form>
<?php endforeach; ?>

<h2>Basket (<?= (int) $totals['item_count'] ?> items)</h2>
<?php if ($basket === []): ?>
    <p>Your basket is empty.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Line subtotal</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($basket as $item): ?>
            <tr>
                <td><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= money((int) $item['price_cents']) ?></td>
                <td>
                    <form method="post" class="inline">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                        <input type="number" name="qty" min="1" value="<?= (int) $item['qty'] ?>">
                        <button type="submit">Update</button>
                    </form>
                </td>
                <td><?= money((int) $item['price_cents'] * (int) $item['qty']) ?></td>
                <td>
                    <form method="post" class="inline">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                        <button type="submit">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Subtotal:</strong> <?= money((int) $totals['subtotal_cents']) ?></p>
<?php endif; ?>

<h2>Checkout</h2>
<form method="post">
    <input type="hidden" name="action" value="checkout">
    <label>
        Name:
        <input type="text" name="name" required>
    </label>
    <label>
        Email:
        <input type="email" name="email" required>
    </label>
    <button type="submit">Checkout</button>
</form>
</body>
</html>
