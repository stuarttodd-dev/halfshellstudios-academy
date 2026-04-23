# Chapter 24 — Visitor (guided practice)

Visitor lifts operations *off* the type hierarchy, so adding a new
operation is one new visitor class without touching any node. It pays
off when nodes are stable but operations grow over time. The trap is
forcing it onto two stable operations and two types — that's just
methods.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Document tree | `Heading`, `Paragraph`, `Image` with html/text/word-count | **Visitor fits** — `MarkdownVisitor` adds a 4th op without edits to nodes |
| 2 — Shape area/perimeter | Two stable ops on two types | **Trap.** Methods on the types are simpler |
| 3 — Game tile analyser | Tiles + 5 ops (cost, colour, sound, AI weight, lighting) | **Visitor fits** — `LightingAbsorptionVisitor` added later, zero node edits |

---

## Exercise 1 — Document visitors

```php
interface DocVisitor {
    public function visitHeading(Heading $h):    mixed;
    public function visitParagraph(Paragraph $p): mixed;
    public function visitImage(Image $i):        mixed;
}
interface Doc { public function accept(DocVisitor $v): mixed; }

new HtmlVisitor(); new PlainTextVisitor(); new WordCountVisitor(); new MarkdownVisitor();
```

Adding `MarkdownVisitor` does not touch `Heading`, `Paragraph`, or
`Image`.

---

## Exercise 2 — Shapes (the trap)

### Verdict — Visitor is the wrong answer

Two types and two stable operations: `area`, `perimeter`. Methods on
the shape are clearer. Visitor would mean an interface, two visitor
classes, two `accept` methods, and a dispatch — to compute `pi r²`.

---

## Exercise 3 — Tile visitors

```php
$tile->accept(new WalkableCostVisitor());
$tile->accept(new RenderColourVisitor());
$tile->accept(new FootstepSoundVisitor());
$tile->accept(new AiPathingWeightVisitor());
$tile->accept(new LightingAbsorptionVisitor()); // added later, no node changes
```

When you suspect "we'll keep adding new analyses to these tiles for
months", that's the Visitor signal.

---

## Chapter rubric

For each non-trap exercise:

- a visitor interface with one method per node type
- each node implements `accept(Visitor)`
- one visitor class per operation
- demonstrate a *new* operation added without touching the nodes

For the trap: explain that two stable ops on two types should be methods.

---

## How to run

```bash
cd php-design-patterns/visitor-chapter-24-guided-practice
php exercise-1-document-visitors/solution.php
php exercise-2-shapes/solution.php
php exercise-3-tile-visitors/solution.php
```
