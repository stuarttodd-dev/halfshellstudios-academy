<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Opaque memento — a value object only `FormState` knows how to read. */
final class FormStateMemento
{
    /** @internal — only accessed through FormState::restore */
    public function __construct(
        public readonly array $fields,
        public readonly ?string $activeStep,
    ) {}
}

final class FormState
{
    /** @var array<string,mixed> */
    private array $fields = [];
    private ?string $activeStep = null;

    public function setField(string $name, mixed $value): void { $this->fields[$name] = $value; }
    public function field(string $name): mixed { return $this->fields[$name] ?? null; }

    public function setActiveStep(?string $step): void { $this->activeStep = $step; }
    public function activeStep(): ?string { return $this->activeStep; }

    public function snapshot(): FormStateMemento
    {
        return new FormStateMemento($this->fields, $this->activeStep);
    }

    public function restore(FormStateMemento $m): void
    {
        $this->fields = $m->fields;
        $this->activeStep = $m->activeStep;
    }
}

/** Caretaker — holds mementos opaquely; never inspects them. */
final class AutosaveManager
{
    private ?FormStateMemento $last = null;

    public function autosave(FormState $form): void
    {
        $this->last = $form->snapshot();
    }

    public function restoreInto(FormState $form): void
    {
        if ($this->last !== null) $form->restore($this->last);
    }

    public function hasSavedState(): bool { return $this->last !== null; }
}

// ---- assertions -------------------------------------------------------------

$form = new FormState();
$form->setField('email', 'a@b.test');
$form->setActiveStep('step-1');

$manager = new AutosaveManager();
$manager->autosave($form);

$form->setField('email', 'changed@b.test');
$form->setActiveStep('step-2');

pdp_assert_eq('changed@b.test', $form->field('email'), 'sanity: changed in memory');
pdp_assert_eq('step-2', $form->activeStep(), 'sanity: step changed');

$manager->restoreInto($form);
pdp_assert_eq('a@b.test', $form->field('email'), 'restored after restoring snapshot');
pdp_assert_eq('step-1', $form->activeStep(), 'active step restored');

// originator's fields are private
$ref = new \ReflectionClass(FormState::class);
foreach ($ref->getProperties() as $p) {
    pdp_assert_true($p->isPrivate(), "FormState::\${$p->getName()} is private");
}

// caretaker holds memento opaquely — see AutosaveManager above:
// it stores `?FormStateMemento` and only forwards it to FormState::restore.

pdp_done();
