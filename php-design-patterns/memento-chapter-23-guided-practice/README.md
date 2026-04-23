# Chapter 23 — Memento (guided practice)

Memento snapshots an originator's private state into an opaque value
object that a caretaker can hold without touching its insides. It pays
off for autosave, undo/redo, and "rewind to checkpoint". The trap is
calling `json_encode` and a Memento.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Form draft autosave | Restore a form to last autosaved version | **Memento fits** — `FormStateMemento` + `AutosaveManager` |
| 2 — Config dump to disk | One-off serialisation | **Trap.** `json_encode` is enough |
| 3 — Spreadsheet undo/redo | Undo and redo stacks of edits | **Memento fits** — `UndoManager` with two stacks |

---

## Exercise 1 — Form autosave

```php
$form = new FormState();
$form->setField('email', 'a@b.test'); $form->setActiveStep('step-1');

$manager = new AutosaveManager();
$manager->autosave($form);

$form->setField('email', 'oops'); $form->setActiveStep('step-2');
$manager->restoreInto($form);     // back to email=a@b.test, step=step-1
```

Caretaker holds the memento opaquely — it never reads its fields.

---

## Exercise 2 — Config dump (the trap)

### Verdict — Memento is the wrong answer

There is no originator with private invariants, no restore step, and
no caretaker. `json_encode($config)` does the job. Adding a Memento
would be ceremony for the sake of a pattern name.

---

## Exercise 3 — Spreadsheet undo/redo

```php
$undo = new UndoManager();
$undo->record($sheet); $sheet->set('A1', 1);
$undo->record($sheet); $sheet->set('A1', 2);
$undo->undo($sheet);   // -> 1
$undo->redo($sheet);   // -> 2
$sheet->set('A1', 99); $undo->record($sheet); // redo stack cleared
```

`Spreadsheet::$cells` stays `private`. The `UndoManager` holds
`SpreadsheetMemento` instances opaquely.

---

## Chapter rubric

For each non-trap exercise:

- an immutable memento value object
- `snapshot()` / `restore()` on the originator
- the originator's fields stay private
- a caretaker holding mementos opaquely

For the trap: explain why JSON is enough.

---

## How to run

```bash
cd php-design-patterns/memento-chapter-23-guided-practice
php exercise-1-form-autosave/solution.php
php exercise-2-config-dump/solution.php
php exercise-3-spreadsheet-undo/solution.php
```
