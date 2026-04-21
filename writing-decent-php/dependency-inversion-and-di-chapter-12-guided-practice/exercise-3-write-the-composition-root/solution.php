<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

/**
 * The "web entry point" — what `public/index.php` would look like in a
 * real app. Notice the absence of `new` calls beyond the container
 * itself. Every entry point looks like this.
 */

$GLOBALS['__sent_mail'] = [];

$container = new AppContainer(config: [
    'STRIPE_KEY' => 'sk_test_solution',
    'SMTP_HOST'  => 'smtp.example.com',
    'SMTP_USER'  => 'mailer',
    'SMTP_PASS'  => 'shh',
]);

/* Web entry point */
$controller = $container->placeOrderController();
$response   = $controller(['customer_id' => 9001, 'total_pence' => 4_500, 'email' => 'alice@example.com']);
echo "controller -> " . json_encode($response) . "\n";

/* Cron entry point — the wiring is identical because it comes from the same container */
$nightly = $container->placeOrder();
$nightly->place(['customer_id' => 9002, 'total_pence' => 1_000, 'email' => 'bob@example.com']);

echo "rows in DB: " . $container->pdoForInspection()->query("SELECT COUNT(*) FROM orders")->fetchColumn() . "\n";
echo "mails sent: " . count($GLOBALS['__sent_mail']) . "\n";
echo "(notice: every entry point asks the container for what it needs; no `new` outside bootstrap.php)\n";
