<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface FormMediator
{
    public function fieldChanged(Field $field, mixed $newValue): void;
    /** @return list<string> field names with errors */
    public function errors(): array;
}

final class Field
{
    private mixed $value = null;
    public function __construct(public readonly string $name, private readonly FormMediator $mediator) {}

    public function set(mixed $value): void
    {
        $this->value = $value;
        $this->mediator->fieldChanged($this, $value);
    }

    public function value(): mixed { return $this->value; }
}

/**
 * Concrete mediator: owns *all* coordination.
 *
 * - When `country` changes, postcode validation rules change.
 * - When `postcode` or `country` changes, validate postcode.
 * - When `email` changes, validate email format.
 *
 * Fields know nothing about each other.
 */
final class CheckoutFormMediator implements FormMediator
{
    /** @var array<string, Field> */
    private array $fields = [];
    /** @var array<string, string> */
    private array $errors = [];

    public function register(Field $field): void { $this->fields[$field->name] = $field; }

    public function fieldChanged(Field $field, mixed $newValue): void
    {
        match ($field->name) {
            'country', 'postcode' => $this->validatePostcode(),
            'email'               => $this->validateEmail(),
            default               => null,
        };
    }

    public function errors(): array { return array_keys($this->errors); }

    private function validatePostcode(): void
    {
        $country = $this->fields['country']->value() ?? null;
        $postcode = $this->fields['postcode']->value() ?? null;
        unset($this->errors['postcode']);
        if ($country === 'UK' && $postcode !== null && !preg_match('/^[A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}$/i', $postcode)) {
            $this->errors['postcode'] = 'invalid UK postcode';
        }
        if ($country === 'US' && $postcode !== null && !preg_match('/^\d{5}$/', $postcode)) {
            $this->errors['postcode'] = 'invalid US zip';
        }
    }

    private function validateEmail(): void
    {
        $email = $this->fields['email']->value() ?? '';
        unset($this->errors['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $this->errors['email'] = 'bad email';
    }
}

// ---- assertions -------------------------------------------------------------

$mediator = new CheckoutFormMediator();

$country = new Field('country', $mediator);
$postcode = new Field('postcode', $mediator);
$email = new Field('email', $mediator);
foreach ([$country, $postcode, $email] as $f) $mediator->register($f);

$country->set('UK');
$postcode->set('SW1A 1AA');
$email->set('a@b.test');
pdp_assert_eq([], $mediator->errors(), 'all valid');

$postcode->set('NOPE');
pdp_assert_eq(['postcode'], $mediator->errors(), 'bad uk postcode flagged');

$country->set('US'); // re-validates postcode against new rules without anyone else moving
pdp_assert_eq(['postcode'], $mediator->errors(), 'us rules now apply, NOPE still bad');

$postcode->set('90210');
pdp_assert_eq([], $mediator->errors(), 'us zip valid');

$email->set('not-an-email');
pdp_assert_eq(['email'], $mediator->errors(), 'bad email flagged');

// fields hold only the mediator
$ref = new \ReflectionClass(Field::class);
$props = array_map(static fn ($p) => $p->getName(), $ref->getProperties());
sort($props);
pdp_assert_eq(['mediator', 'name', 'value'], $props, 'Field has no references to other fields');

pdp_done();
