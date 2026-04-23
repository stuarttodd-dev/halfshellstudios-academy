<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class SpreadsheetMemento
{
    /** @param array<string,mixed> $cells */
    public function __construct(
        public readonly array $cells,
        public readonly ?string $selection,
    ) {}
}

final class Spreadsheet
{
    /** @var array<string, mixed> */
    private array $cells = [];
    private ?string $selection = null;

    public function set(string $cell, mixed $value): void { $this->cells[$cell] = $value; }
    public function get(string $cell): mixed { return $this->cells[$cell] ?? null; }
    public function select(string $cell): void { $this->selection = $cell; }
    public function selection(): ?string { return $this->selection; }

    public function snapshot(): SpreadsheetMemento { return new SpreadsheetMemento($this->cells, $this->selection); }
    public function restore(SpreadsheetMemento $m): void
    {
        $this->cells = $m->cells;
        $this->selection = $m->selection;
    }
}

final class UndoManager
{
    /** @var list<SpreadsheetMemento> */
    private array $undoStack = [];
    /** @var list<SpreadsheetMemento> */
    private array $redoStack = [];

    public function record(Spreadsheet $sheet): void
    {
        $this->undoStack[] = $sheet->snapshot();
        $this->redoStack = [];
    }

    public function undo(Spreadsheet $sheet): void
    {
        if ($this->undoStack === []) return;
        $this->redoStack[] = $sheet->snapshot();
        $sheet->restore(array_pop($this->undoStack));
    }

    public function redo(Spreadsheet $sheet): void
    {
        if ($this->redoStack === []) return;
        $this->undoStack[] = $sheet->snapshot();
        $sheet->restore(array_pop($this->redoStack));
    }

    public function undoDepth(): int { return count($this->undoStack); }
    public function redoDepth(): int { return count($this->redoStack); }
}

// ---- assertions -------------------------------------------------------------

$sheet = new Spreadsheet();
$undo = new UndoManager();

$undo->record($sheet);
$sheet->set('A1', 1);

$undo->record($sheet);
$sheet->set('A1', 2);

$undo->record($sheet);
$sheet->set('A1', 3);
$sheet->select('A1');

pdp_assert_eq(3, $sheet->get('A1'), 'current value is 3');

$undo->undo($sheet);
pdp_assert_eq(2, $sheet->get('A1'), 'undo to 2');
$undo->undo($sheet);
pdp_assert_eq(1, $sheet->get('A1'), 'undo to 1');

$undo->redo($sheet);
pdp_assert_eq(2, $sheet->get('A1'), 'redo back to 2');

// new edit clears redo stack
$sheet->set('A1', 99);
$undo->record($sheet);
pdp_assert_eq(0, $undo->redoDepth(), 'new edit clears redo on next record');

// originator's data stays private
$ref = new \ReflectionClass(Spreadsheet::class);
foreach ($ref->getProperties() as $p) {
    pdp_assert_true($p->isPrivate(), "Spreadsheet::\${$p->getName()} is private");
}

pdp_done();
