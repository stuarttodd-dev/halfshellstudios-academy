<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class PaymentFailed extends \DomainException {}

interface CheckoutMediator
{
    public function paymentSucceeded(string $orderId, int $amountInPence): void;
    public function paymentFailed(string $orderId, string $reason): void;
}

final class PaymentService
{
    public function __construct(private readonly CheckoutMediator $mediator) {}

    public function chargeOrFail(string $orderId, int $amountInPence, bool $shouldSucceed): void
    {
        if ($shouldSucceed) {
            $this->mediator->paymentSucceeded($orderId, $amountInPence);
        } else {
            $this->mediator->paymentFailed($orderId, 'card declined');
        }
    }
}

final class EmailService
{
    /** @var list<string> */
    public array $sent = [];
    public function sendReceipt(string $orderId): void { $this->sent[] = "receipt:{$orderId}"; }
    public function sendFailureNotice(string $orderId): void { $this->sent[] = "failure:{$orderId}"; }
}

final class AnalyticsService
{
    /** @var list<string> */
    public array $events = [];
    public function record(string $event, string $orderId): void { $this->events[] = "{$event}:{$orderId}"; }
}

final class AuditService
{
    /** @var list<string> */
    public array $entries = [];
    public function log(string $entry): void { $this->entries[] = $entry; }
}

final class InventoryService
{
    /** @var array<string,int> */
    public array $reserved = [];
    public function reserve(string $orderId, int $qty): void { $this->reserved[$orderId] = $qty; }
    public function release(string $orderId): void { unset($this->reserved[$orderId]); }
}

/**
 * Concrete mediator: the entire checkout flow lives here.
 * Components only know the mediator (or are called by it).
 */
final class DefaultCheckoutMediator implements CheckoutMediator
{
    private ?PaymentService $payments = null;

    public function __construct(
        private readonly EmailService $email,
        private readonly AnalyticsService $analytics,
        private readonly AuditService $audit,
        private readonly InventoryService $inventory,
    ) {}

    public function setPayments(PaymentService $p): void { $this->payments = $p; }

    public function checkout(string $orderId, int $qty, int $amountInPence, bool $paymentShouldSucceed): void
    {
        $this->inventory->reserve($orderId, $qty);
        $this->audit->log("checkout-start:{$orderId}");
        $this->payments->chargeOrFail($orderId, $amountInPence, $paymentShouldSucceed);
    }

    public function paymentSucceeded(string $orderId, int $amountInPence): void
    {
        $this->email->sendReceipt($orderId);
        $this->analytics->record('checkout.completed', $orderId);
        $this->audit->log("paid:{$orderId}:{$amountInPence}");
    }

    public function paymentFailed(string $orderId, string $reason): void
    {
        $this->inventory->release($orderId);
        $this->email->sendFailureNotice($orderId);
        $this->analytics->record('checkout.failed', $orderId);
        $this->audit->log("failed:{$orderId}:{$reason}");
    }
}

// ---- wiring (composition root) ---------------------------------------------

$email = new EmailService();
$analytics = new AnalyticsService();
$audit = new AuditService();
$inventory = new InventoryService();
$mediator = new DefaultCheckoutMediator($email, $analytics, $audit, $inventory);
$mediator->setPayments(new PaymentService($mediator));

// ---- assertions -------------------------------------------------------------

$mediator->checkout('o1', qty: 2, amountInPence: 5_000, paymentShouldSucceed: true);

pdp_assert_eq(['receipt:o1'], $email->sent, 'receipt sent on success');
pdp_assert_eq(['checkout.completed:o1'], $analytics->events, 'success analytics recorded');
pdp_assert_eq(['checkout-start:o1', 'paid:o1:5000'], $audit->entries, 'success audit trail');
pdp_assert_eq(['o1' => 2], $inventory->reserved, 'inventory still reserved');

$email->sent = []; $analytics->events = []; $audit->entries = [];

$mediator->checkout('o2', qty: 1, amountInPence: 2_500, paymentShouldSucceed: false);

pdp_assert_eq(['failure:o2'], $email->sent, 'failure notice sent');
pdp_assert_eq(['checkout.failed:o2'], $analytics->events, 'failure analytics recorded');
pdp_assert_eq(['checkout-start:o2', 'failed:o2:card declined'], $audit->entries, 'failure audit trail');
pdp_assert_eq(['o1' => 2], $inventory->reserved, 'inventory rolled back for o2 only');

// component isolation: PaymentService doesn't know about email/analytics/audit
$ref = new \ReflectionClass(PaymentService::class);
$deps = array_map(static fn ($p) => $p->getType()?->getName(), $ref->getProperties());
pdp_assert_eq([CheckoutMediator::class], $deps, 'PaymentService depends only on the mediator');

pdp_done();
