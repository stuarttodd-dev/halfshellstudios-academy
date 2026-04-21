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
 * The base class peeks at its own subclass with `$this instanceof X`.
 * Two smells in one:
 *   - Adding a third audience (`PartnerUser`) means editing the base.
 *   - Subclasses are forced to be empty marker types — they exist only
 *     so the base can `instanceof` them.
 */
abstract class User
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
    ) {}

    public function welcomeEmail(): EmailMessage
    {
        if ($this instanceof CustomerUser) {
            return new EmailMessage(
                to:      $this->email,
                subject: 'Welcome to the shop',
                body:    "Hi {$this->name}, here's £5 off your first order.",
            );
        }

        if ($this instanceof BusinessUser) {
            return new EmailMessage(
                to:      $this->email,
                subject: 'Welcome to the business portal',
                body:    "Hi {$this->name}, your account manager will be in touch shortly.",
            );
        }

        return new EmailMessage(
            to:      $this->email,
            subject: 'Welcome',
            body:    "Hi {$this->name}.",
        );
    }
}

final class CustomerUser extends User {}
final class BusinessUser extends User {}
final class GenericUser  extends User {}

/* ---------- driver ---------- */

$users = [
    new CustomerUser(email: 'alice@example.com', name: 'Alice'),
    new BusinessUser(email: 'bob@business.com',  name: 'Bob'),
    new GenericUser (email: 'eve@example.com',   name: 'Eve'),
];

foreach ($users as $user) {
    $message = $user->welcomeEmail();
    echo "to={$message->to} | subj=\"{$message->subject}\" | body=\"{$message->body}\"\n";
}
