<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class CustomerRepository
{
    public function byEmail(string $email): Customer|false
    {
        $row = DB::table('customers')->where('email', $email)->first();

        return $row === null ? false : Customer::fromRow($row);
    }
}

/* ---------- three call sites that all use the same `byEmail` ---------- */

/**
 * 1) Profile controller. The customer must exist; missing means 404.
 */
final class CustomerProfileController
{
    public function __construct(private CustomerRepository $customers) {}

    public function show(string $email): JsonResponse
    {
        $customer = $this->customers->byEmail($email);

        if ($customer === false) {
            return new JsonResponse(['error' => 'customer_not_found'], 404);
        }

        return new JsonResponse(['id' => $customer->id, 'name' => $customer->name]);
    }
}

/**
 * 2) Background marketing job. Missing customer is *not* an error —
 * the email may have been bounced, the row may have been deleted —
 * we just skip and move on.
 */
final class SendMarketingCampaignJob
{
    public function __construct(
        private CustomerRepository $customers,
        private MarketingMailer    $mailer,
    ) {}

    public function runFor(string $email): void
    {
        $customer = $this->customers->byEmail($email);

        if ($customer === false) {
            return;
        }

        if (! $customer->marketingOptIn) {
            return;
        }

        $this->mailer->sendCampaignTo($customer);
    }
}

/**
 * 3) Login flow. Missing customer is the *expected* path on first login
 * — we want to upsert: read, and if absent, insert.
 */
final class UpsertOnLogin
{
    public function __construct(private CustomerRepository $customers) {}

    public function login(string $email, string $name): Customer
    {
        $existing = $this->customers->byEmail($email);

        if ($existing !== false) {
            return $existing;
        }

        return new Customer(id: 9_999, email: $email, name: $name, marketingOptIn: false);
    }
}

/* ---------- driver ---------- */

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
