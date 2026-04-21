<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.test.php';

/**
 * A millisecond-fast test that exercises the full use case with no
 * `PDO`, no Stripe SDK, no SMTP. Same object graph as production,
 * different leaves.
 */

$container = new TestContainer();
$useCase   = $container->placeOrder();

$orderId = $useCase->place([
    'customer_id' => 9001,
    'total_pence' => 4_500,
    'email'       => 'alice@example.com',
]);

assert($orderId->value === 1);
assert($container->orders->rows === [1 => ['customer_id' => 9001, 'total_pence' => 4_500]]);
assert($container->payments->charges === [['amount' => 4_500, 'description' => 'Order #1']]);
assert($container->mailer->sent === [['to' => 'alice@example.com', 'order_id' => 1, 'amount' => 4_500]]);

echo "test passed.\n";
echo "  orders:   " . json_encode($container->orders->rows)     . "\n";
echo "  payments: " . json_encode($container->payments->charges) . "\n";
echo "  mailer:   " . json_encode($container->mailer->sent)     . "\n";
