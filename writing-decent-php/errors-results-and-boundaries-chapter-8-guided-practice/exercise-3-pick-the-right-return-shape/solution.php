<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Domain failure raised by `byEmailOrFail` when the caller has stated
 * that absence is unacceptable. Carrying the email makes the exception
 * useful at the boundary without having to guess what was looked up.
 */
final class CustomerNotFoundException extends \DomainException
{
    public function __construct(public readonly string $email)
    {
        parent::__construct("Customer with email {$email} not found.");
    }
}

/**
 * Two methods, each honest about its return shape. No `false`-as-sentinel,
 * no overloading of one return type to mean two different things, no
 * "did the caller remember to check?" foot-guns.
 */
final class CustomerRepository
{
    public function byEmailOrNull(string $email): ?Customer
    {
        $row = DB::table('customers')->where('email', $email)->first();

        return $row === null ? null : Customer::fromRow($row);
    }

    public function byEmailOrFail(string $email): Customer
    {
        return $this->byEmailOrNull($email)
            ?? throw new CustomerNotFoundException($email);
    }
}

/**
 * 1) Profile controller. Missing-is-an-error → orFail + boundary catch.
 *    The controller body now reads as a single happy path; the failure
 *    case lives in a `catch`, not a defensive `if`.
 */
final class CustomerProfileController
{
    public function __construct(private CustomerRepository $customers) {}

    public function show(string $email): JsonResponse
    {
        try {
            $customer = $this->customers->byEmailOrFail($email);
        } catch (CustomerNotFoundException) {
            return new JsonResponse(['error' => 'customer_not_found'], 404);
        }

        return new JsonResponse(['id' => $customer->id, 'name' => $customer->name]);
    }
}

/**
 * 2) Background marketing job. Missing-is-fine → orNull and skip.
 *    Notice how the two precondition checks now line up as a tidy
 *    pair of guard clauses, without the false vs null distraction.
 */
final class SendMarketingCampaignJob
{
    public function __construct(
        private CustomerRepository $customers,
        private MarketingMailer    $mailer,
    ) {}

    public function runFor(string $email): void
    {
        $customer = $this->customers->byEmailOrNull($email);

        if ($customer === null)            { return; }
        if (! $customer->marketingOptIn)   { return; }

        $this->mailer->sendCampaignTo($customer);
    }
}

/**
 * 3) Login flow. Missing-is-the-expected-path → orNull, fall through
 *    to the "create" branch when null. The `?? new Customer(...)`
 *    expresses the upsert in one line.
 */
final class UpsertOnLogin
{
    public function __construct(private CustomerRepository $customers) {}

    public function login(string $email, string $name): Customer
    {
        return $this->customers->byEmailOrNull($email)
            ?? new Customer(id: 9_999, email: $email, name: $name, marketingOptIn: false);
    }
}

/* ---------- driver (identical scenarios to starter.php) ---------- */

$repo    = new CustomerRepository();
$profile = new CustomerProfileController($repo);
$job     = new SendMarketingCampaignJob($repo, new MarketingMailer());
$upsert  = new UpsertOnLogin($repo);

MarketingMailer::reset();

foreach (['alice@example.com', 'ghost@example.com'] as $email) {
    $response = $profile->show($email);
    printf("profile %-22s -> %d %s\n", $email, $response->status, json_encode($response->data));
}

foreach (['alice@example.com', 'bob@example.com', 'ghost@example.com'] as $email) {
    $job->runFor($email);
}
printf("marketing sent to: %s\n", json_encode(MarketingMailer::$sent));

$existing = $upsert->login('alice@example.com', 'Alice Cooper');
$created  = $upsert->login('newbie@example.com', 'Newbie');
printf("upsert existing -> id=%d name=%s\n", $existing->id, $existing->name);
printf("upsert new      -> id=%d name=%s\n", $created->id,  $created->name);
