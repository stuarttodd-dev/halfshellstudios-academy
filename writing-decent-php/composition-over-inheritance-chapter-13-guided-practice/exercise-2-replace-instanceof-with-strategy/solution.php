<?php
declare(strict_types=1);

final class EmailMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $body,
    ) {}
}

/**
 * Composition design choices made here:
 *
 *   1. `User` becomes ONE concrete class with an `audience` field.
 *      The empty marker subclasses (`CustomerUser`, `BusinessUser`,
 *      `GenericUser`) are deleted — they only existed so the base
 *      could `instanceof` them.
 *
 *   2. The audience-specific copy lives in a `WelcomeTemplate`
 *      strategy, with one implementation per audience.
 *
 *   3. A small `WelcomeTemplateRegistry` picks the template for a
 *      given audience and falls back to a default. Adding a third
 *      audience means: declare an enum case + add one new template +
 *      register it. Nothing else moves.
 *
 *   4. The "compose a welcome email for a user" workflow lives in a
 *      use-case class (`SendWelcomeEmail`), not on the user. The user
 *      stays as data; the policy stays as a strategy.
 */

enum Audience: string
{
    case Customer = 'customer';
    case Business = 'business';
    case Generic  = 'generic';
}

final class User
{
    public function __construct(
        public readonly string   $email,
        public readonly string   $name,
        public readonly Audience $audience,
    ) {}
}

interface WelcomeTemplate
{
    public function compose(User $user): EmailMessage;
}

final class CustomerWelcomeTemplate implements WelcomeTemplate
{
    public function compose(User $user): EmailMessage
    {
        return new EmailMessage(
            to:      $user->email,
            subject: 'Welcome to the shop',
            body:    "Hi {$user->name}, here's £5 off your first order.",
        );
    }
}

final class BusinessWelcomeTemplate implements WelcomeTemplate
{
    public function compose(User $user): EmailMessage
    {
        return new EmailMessage(
            to:      $user->email,
            subject: 'Welcome to the business portal',
            body:    "Hi {$user->name}, your account manager will be in touch shortly.",
        );
    }
}

final class GenericWelcomeTemplate implements WelcomeTemplate
{
    public function compose(User $user): EmailMessage
    {
        return new EmailMessage(
            to:      $user->email,
            subject: 'Welcome',
            body:    "Hi {$user->name}.",
        );
    }
}

final class WelcomeTemplateRegistry
{
    /** @param array<string, WelcomeTemplate> $templates keyed by Audience::value */
    public function __construct(
        private array           $templates,
        private WelcomeTemplate $default,
    ) {}

    public function for(Audience $audience): WelcomeTemplate
    {
        return $this->templates[$audience->value] ?? $this->default;
    }
}

final class SendWelcomeEmail
{
    public function __construct(private WelcomeTemplateRegistry $templates) {}

    public function for(User $user): EmailMessage
    {
        return $this->templates->for($user->audience)->compose($user);
    }
}

/* ---------- driver (identical observable output to starter.php) ---------- */

$registry = new WelcomeTemplateRegistry(
    templates: [
        Audience::Customer->value => new CustomerWelcomeTemplate(),
        Audience::Business->value => new BusinessWelcomeTemplate(),
    ],
    default: new GenericWelcomeTemplate(),
);

$useCase = new SendWelcomeEmail($registry);

$users = [
    new User(email: 'alice@example.com', name: 'Alice', audience: Audience::Customer),
    new User(email: 'bob@business.com',  name: 'Bob',   audience: Audience::Business),
    new User(email: 'eve@example.com',   name: 'Eve',   audience: Audience::Generic),
];

foreach ($users as $user) {
    $message = $useCase->for($user);
    echo "to={$message->to} | subj=\"{$message->subject}\" | body=\"{$message->body}\"\n";
}
