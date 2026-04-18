<?php
declare(strict_types=1);

session_start();
$_SESSION['basket'] ??= [
    ['product_id' => 1, 'name' => 'T-Shirt', 'price_cents' => 1999, 'qty' => 1],
    ['product_id' => 2, 'name' => 'Mug', 'price_cents' => 1299, 'qty' => 1],
];

function updateQuantity(array &$basket, int $id, int $qty): bool
{
    if ($qty < 1) {
        return false;
    }
    foreach ($basket as &$item) {
        if ((int) $item['product_id'] === $id) {
            $item['qty'] = $qty;
            return true;
        }
    }
    return false;
}

function removeItem(array &$basket, int $id): void
{
    $basket = array_values(array_filter($basket, static fn(array $item): bool => (int) $item['product_id'] !== $id));
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['product_id'] ?? 0);
    if ($action === 'update') {
        $qty = (int) ($_POST['qty'] ?? 0);
        $message = updateQuantity($_SESSION['basket'], $id, $qty) ? 'Quantity updated.' : 'Quantity must be >= 1.';
    } elseif ($action === 'remove') {
        removeItem($_SESSION['basket'], $id);
        $message = 'Item removed.';
    }
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Basket Update Remove</title></head>
<body>
<h1>Basket update quantities and remove items</h1>
<?php if ($message !== ''): ?><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<?php foreach ($_SESSION['basket'] as $item): ?>
    <div style="margin-bottom:8px;">
        <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
        <form method="post" style="display:inline">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
            <input type="number" name="qty" min="1" value="<?= (int) $item['qty'] ?>">
            <button type="submit">Update</button>
        </form>
        <form method="post" style="display:inline">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
            <button type="submit">Remove</button>
        </form>
    </div>
<?php endforeach; ?>
</body>
</html>
