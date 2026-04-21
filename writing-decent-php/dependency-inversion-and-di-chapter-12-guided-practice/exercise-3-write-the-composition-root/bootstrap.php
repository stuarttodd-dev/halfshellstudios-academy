<?php
declare(strict_types=1);

require_once __DIR__ . '/support/classes.php';

/**
 * The composition root.
 *
 * One file. Reads configuration. Constructs every adapter and every
 * use case. Hands the assembled object graph back to whatever entry
 * point asked for it. **The only `new` calls in the whole application
 * (other than for value objects) live in this file.**
 *
 * Why this matters:
 *   - swap an adapter (PDO -> Doctrine, SMTP -> SES, Stripe -> mock)
 *     by editing one line here;
 *   - tests bypass this file entirely and wire their own doubles in
 *     the same shape (see `bootstrap.test.php`);
 *   - the list of dependencies your app has is *one file long*.
 *
 * For a real Laravel/Symfony project, the framework's container plays
 * this role. The lesson holds: the wiring lives in one named place,
 * not smeared across controllers.
 */

final class AppContainer
{
    private ?PDO                  $pdo        = null;
    private ?\Stripe\StripeClient $stripe     = null;
    private ?OrderRepository      $orders     = null;
    private ?PaymentGateway       $payments   = null;
    private ?ReceiptMailer        $mailer     = null;
    private ?PlaceOrder           $placeOrder = null;

    /** @param array<string, string> $config */
    public function __construct(private array $config) {}

    public function placeOrderController(): PlaceOrderController
    {
        return new PlaceOrderController($this->placeOrder());
    }

    public function placeOrder(): PlaceOrder
    {
        return $this->placeOrder ??= new PlaceOrder(
            $this->orderRepository(),
            $this->paymentGateway(),
            $this->receiptMailer(),
        );
    }

    /* ---------- adapters (constructed once, shared) ---------- */

    private function orderRepository(): OrderRepository
    {
        return $this->orders ??= new PdoOrderRepository($this->pdo());
    }

    private function paymentGateway(): PaymentGateway
    {
        return $this->payments ??= new StripePaymentGateway($this->stripe());
    }

    private function receiptMailer(): ReceiptMailer
    {
        return $this->mailer ??= new SmtpReceiptMailer(
            host: $this->config['SMTP_HOST'],
            user: $this->config['SMTP_USER'],
            pass: $this->config['SMTP_PASS'],
        );
    }

    /* ---------- low-level resources ---------- */

    private function pdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new PDO('sqlite::memory:');
            $this->pdo->exec('CREATE TABLE orders (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER, total_pence INTEGER)');
        }
        return $this->pdo;
    }

    public function pdoForInspection(): PDO
    {
        return $this->pdo();
    }

    private function stripe(): \Stripe\StripeClient
    {
        return $this->stripe ??= new \Stripe\StripeClient($this->config['STRIPE_KEY']);
    }
}
