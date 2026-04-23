<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class CustomerId
{
    public function __construct(public readonly int $value) {}
}

final class Customer
{
    public function __construct(
        public readonly CustomerId $id,
        public readonly string $email,
        public readonly string $name,
    ) {}
}

interface CustomerRepository
{
    public function find(CustomerId $id): ?Customer;
}

/** Real implementation over PDO — kept here as a sketch for shape. */
final class PdoCustomerRepository implements CustomerRepository
{
    public function __construct(private readonly \PDO $db) {}

    public function find(CustomerId $id): ?Customer
    {
        $stmt = $this->db->prepare('SELECT id, email, name FROM customers WHERE id = ?');
        $stmt->execute([$id->value]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;
        return new Customer(new CustomerId((int) $row['id']), (string) $row['email'], (string) $row['name']);
    }
}

/** In-memory implementation — used in tests and at the dev composition root. */
final class InMemoryCustomerRepository implements CustomerRepository
{
    /** @param array<int, Customer> $customersById */
    public function __construct(private array $customersById = []) {}

    public function add(Customer $customer): void { $this->customersById[$customer->id->value] = $customer; }
    public function find(CustomerId $id): ?Customer { return $this->customersById[$id->value] ?? null; }
}

interface Mailer
{
    public function send(string $to, string $subject, string $body): void;
}

final class RecordingMailer implements Mailer
{
    /** @var list<array{to:string,subject:string,body:string}> */
    public array $sent = [];
    public function send(string $to, string $subject, string $body): void { $this->sent[] = compact('to', 'subject', 'body'); }
}

final class CustomerNotFound extends \RuntimeException {}

/** Caller depends on the repository INTERFACE — no SQL, no PDO. */
final class WelcomeService
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly Mailer $mailer,
    ) {}

    public function welcome(CustomerId $id): void
    {
        $customer = $this->customers->find($id) ?? throw new CustomerNotFound("Customer not found: {$id->value}");
        $this->mailer->send($customer->email, 'Welcome', "Hello {$customer->name}!");
    }
}

// ---- assertions -------------------------------------------------------------

$alice = new Customer(new CustomerId(1), 'alice@example.com', 'Alice');
$repo  = new InMemoryCustomerRepository([1 => $alice]);
$mailer = new RecordingMailer();

(new WelcomeService($repo, $mailer))->welcome(new CustomerId(1));
pdp_assert_eq(1, count($mailer->sent), 'WelcomeService sent one email');
pdp_assert_eq('alice@example.com', $mailer->sent[0]['to'], 'addressed to the customer');
pdp_assert_eq('Hello Alice!',       $mailer->sent[0]['body'], 'greeted by name');

pdp_assert_throws(
    CustomerNotFound::class,
    fn () => (new WelcomeService($repo, $mailer))->welcome(new CustomerId(999)),
    'unknown customer raises a domain exception (not a generic RuntimeException string)',
);

pdp_done();
