<?php
declare(strict_types=1);

require_once __DIR__ . '/support/classes.php';

/**
 * The TEST composition root.
 *
 * Same shape as production — but every adapter is a deterministic
 * double. Tests never construct a `PDO`, never instantiate Stripe,
 * never open an SMTP socket. The container is what makes "wire test
 * doubles in the same way as production wires real adapters" easy to
 * read.
 */

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var array<int, array{customer_id: int, total_pence: int}> */
    public array $rows = [];
    private int  $next = 1;

    /** @param array{customer_id: int, total_pence: int} $row */
    public function save(array $row): OrderId
    {
        $id = $this->next++;
        $this->rows[$id] = $row;
        return new OrderId($id);
    }
}

final class RecordingPaymentGateway implements PaymentGateway
{
    /** @var list<array{amount: int, description: string}> */
    public array $charges = [];

    public function charge(int $amountInPence, string $description): string
    {
        $this->charges[] = ['amount' => $amountInPence, 'description' => $description];
        return 'ch_test_' . count($this->charges);
    }
}

final class RecordingReceiptMailer implements ReceiptMailer
{
    /** @var list<array{to: string, order_id: int, amount: int}> */
    public array $sent = [];

    public function sendReceipt(string $to, OrderId $orderId, int $amountInPence): void
    {
        $this->sent[] = ['to' => $to, 'order_id' => $orderId->value, 'amount' => $amountInPence];
    }
}

final class TestContainer
{
    public InMemoryOrderRepository $orders;
    public RecordingPaymentGateway $payments;
    public RecordingReceiptMailer  $mailer;

    public function __construct()
    {
        $this->orders   = new InMemoryOrderRepository();
        $this->payments = new RecordingPaymentGateway();
        $this->mailer   = new RecordingReceiptMailer();
    }

    public function placeOrder(): PlaceOrder
    {
        return new PlaceOrder($this->orders, $this->payments, $this->mailer);
    }
}
