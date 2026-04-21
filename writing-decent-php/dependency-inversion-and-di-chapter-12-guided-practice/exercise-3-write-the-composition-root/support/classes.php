<?php
declare(strict_types=1);

/**
 * The classes the brief gives us, fleshed out just enough to be runnable.
 * In a real project each of these lives in its own file under `src/`.
 */

namespace {

    /* ---------- domain types ---------- */

    final class OrderId
    {
        public function __construct(public readonly int $value) {}
    }

    /* ---------- ports (interfaces) ---------- */

    interface OrderRepository
    {
        /** @param array{customer_id: int, total_pence: int} $row */
        public function save(array $row): OrderId;
    }

    interface PaymentGateway
    {
        public function charge(int $amountInPence, string $description): string;
    }

    interface ReceiptMailer
    {
        public function sendReceipt(string $to, OrderId $orderId, int $amountInPence): void;
    }

    /* ---------- adapters (implementations) ---------- */

    final class PdoOrderRepository implements OrderRepository
    {
        public function __construct(private PDO $pdo) {}

        /** @param array{customer_id: int, total_pence: int} $row */
        public function save(array $row): OrderId
        {
            $this->pdo->exec(sprintf(
                "INSERT INTO orders(customer_id, total_pence) VALUES(%d, %d)",
                $row['customer_id'],
                $row['total_pence'],
            ));
            return new OrderId((int) $this->pdo->lastInsertId());
        }
    }

    final class StripePaymentGateway implements PaymentGateway
    {
        public function __construct(private \Stripe\StripeClient $stripe) {}

        public function charge(int $amountInPence, string $description): string
        {
            return $this->stripe->charges->create(['amount' => $amountInPence, 'description' => $description])->id;
        }
    }

    final class SmtpReceiptMailer implements ReceiptMailer
    {
        public function __construct(
            private string $host,
            private string $user,
            private string $pass,
        ) {}

        public function sendReceipt(string $to, OrderId $orderId, int $amountInPence): void
        {
            // In a real SMTP mailer we would open a connection here. We keep
            // a record so the example is observable.
            $GLOBALS['__sent_mail'][] = [
                'host'    => $this->host,
                'user'    => $this->user,
                'to'      => $to,
                'subject' => "Receipt for order #{$orderId->value}",
                'body'    => "Thanks! You paid {$amountInPence}p.",
            ];
        }
    }

    /* ---------- use case + controller ---------- */

    final class PlaceOrder
    {
        public function __construct(
            private OrderRepository $orders,
            private PaymentGateway  $payments,
            private ReceiptMailer   $mailer,
        ) {}

        /** @param array{customer_id: int, total_pence: int, email: string} $request */
        public function place(array $request): OrderId
        {
            $orderId = $this->orders->save([
                'customer_id' => $request['customer_id'],
                'total_pence' => $request['total_pence'],
            ]);

            $this->payments->charge($request['total_pence'], "Order #{$orderId->value}");
            $this->mailer->sendReceipt($request['email'], $orderId, $request['total_pence']);

            return $orderId;
        }
    }

    final class PlaceOrderController
    {
        public function __construct(private PlaceOrder $useCase) {}

        /** @param array{customer_id: int, total_pence: int, email: string} $request */
        public function __invoke(array $request): array
        {
            $orderId = $this->useCase->place($request);
            return ['status' => 'ok', 'order_id' => $orderId->value];
        }
    }

    /* ---------- in-process stubs so the example runs without a DB / SMTP / Stripe ---------- */

    if (! class_exists(PDO::class)) {
        // Real PDO ships with PHP — this branch only fires in environments where it doesn't.
    }
}

namespace Stripe {
    final class StripeClient
    {
        public Charges $charges;
        public function __construct(public readonly string $apiKey) { $this->charges = new Charges(); }
    }

    final class Charges
    {
        /** @var list<array<string, mixed>> */
        public array $created = [];
        /** @param array<string, mixed> $params */
        public function create(array $params): object
        {
            $id = sprintf('ch_%05d', count($this->created) + 1);
            $this->created[] = $params + ['id' => $id];
            return (object) ['id' => $id];
        }
    }
}
