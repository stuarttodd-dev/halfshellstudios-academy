<?php
declare(strict_types=1);

require_once __DIR__ . '/support/classes.php';

/**
 * What "no composition root" looks like in practice.
 *
 * Every entry point — every controller, every console command, every
 * webhook handler — has to remember:
 *   - which adapter implements which port
 *   - what credentials each adapter takes
 *   - the construction order
 *
 * The result is `new` calls smeared across the codebase. Change the
 * Stripe API key location, or swap PDO for Doctrine, and you grep for
 * weeks.
 */

$_ENV = [
    'STRIPE_KEY' => 'sk_test_starter',
    'SMTP_HOST'  => 'smtp.example.com',
    'SMTP_USER'  => 'mailer',
    'SMTP_PASS'  => 'shh',
];
$GLOBALS['__sent_mail'] = [];
$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE orders (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER, total_pence INTEGER)');

/* Entry point #1: web controller */
$controller = new PlaceOrderController(
    new PlaceOrder(
        new PdoOrderRepository($pdo),
        new StripePaymentGateway(new \Stripe\StripeClient($_ENV['STRIPE_KEY'])),
        new SmtpReceiptMailer($_ENV['SMTP_HOST'], $_ENV['SMTP_USER'], $_ENV['SMTP_PASS']),
    ),
);

$response = $controller(['customer_id' => 9001, 'total_pence' => 4_500, 'email' => 'alice@example.com']);
echo "controller -> " . json_encode($response) . "\n";

/* Entry point #2: a cron command duplicating the same wiring */
$nightly = new PlaceOrder(
    new PdoOrderRepository($pdo),
    new StripePaymentGateway(new \Stripe\StripeClient($_ENV['STRIPE_KEY'])),
    new SmtpReceiptMailer($_ENV['SMTP_HOST'], $_ENV['SMTP_USER'], $_ENV['SMTP_PASS']),
);
$nightly->place(['customer_id' => 9002, 'total_pence' => 1_000, 'email' => 'bob@example.com']);

echo "rows in DB: " . $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() . "\n";
echo "mails sent: " . count($GLOBALS['__sent_mail']) . "\n";
echo "(notice: every entry point repeats the wiring; nothing is the single source of truth)\n";
