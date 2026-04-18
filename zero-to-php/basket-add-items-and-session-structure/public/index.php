<?php
declare(strict_types=1);

session_start();
$_SESSION['basket'] ??= [];

$products = [
    1 => ['id' => 1, 'name' => 'T-Shirt', 'price_cents' => 1999],
    2 => ['id' => 2, 'name' => 'Mug', 'price_cents' => 1299],
];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['product_id'] ?? 0);
    if (isset($products[$id])) {
        addToBasket($_SESSION['basket'], $products[$id]);
    }
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Basket Add Items</title></head>
<body>
<h1>Basket add items and session structure</h1>
<?php foreach ($products as $product): ?>
    <form method="post" style="display:inline">
        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <button type="submit">Add <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></button>
    </form>
<?php endforeach; ?>

<pre><?= htmlspecialchars(json_encode($_SESSION['basket'], JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') ?></pre>
</body>
</html>
