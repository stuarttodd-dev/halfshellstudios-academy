<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface DocVisitor
{
    public function visitHeading(Heading $h): mixed;
    public function visitParagraph(Paragraph $p): mixed;
    public function visitImage(Image $i): mixed;
}

interface Doc
{
    public function accept(DocVisitor $v): mixed;
}

final class Heading implements Doc
{
    public function __construct(public readonly string $text, public readonly int $level = 1) {}
    public function accept(DocVisitor $v): mixed { return $v->visitHeading($this); }
}

final class Paragraph implements Doc
{
    public function __construct(public readonly string $text) {}
    public function accept(DocVisitor $v): mixed { return $v->visitParagraph($this); }
}

final class Image implements Doc
{
    public function __construct(public readonly string $url, public readonly string $alt = '') {}
    public function accept(DocVisitor $v): mixed { return $v->visitImage($this); }
}

final class HtmlVisitor implements DocVisitor
{
    public function visitHeading(Heading $h): string { return "<h{$h->level}>{$h->text}</h{$h->level}>"; }
    public function visitParagraph(Paragraph $p): string { return "<p>{$p->text}</p>"; }
    public function visitImage(Image $i): string { return "<img src=\"{$i->url}\" alt=\"{$i->alt}\">"; }
}

final class PlainTextVisitor implements DocVisitor
{
    public function visitHeading(Heading $h): string { return strtoupper($h->text); }
    public function visitParagraph(Paragraph $p): string { return $p->text; }
    public function visitImage(Image $i): string { return "[image: {$i->alt}]"; }
}

final class WordCountVisitor implements DocVisitor
{
    public function visitHeading(Heading $h): int { return str_word_count($h->text); }
    public function visitParagraph(Paragraph $p): int { return str_word_count($p->text); }
    public function visitImage(Image $i): int { return str_word_count($i->alt); }
}

/** New visitor — added without touching Heading/Paragraph/Image. */
final class MarkdownVisitor implements DocVisitor
{
    public function visitHeading(Heading $h): string { return str_repeat('#', $h->level) . ' ' . $h->text; }
    public function visitParagraph(Paragraph $p): string { return $p->text; }
    public function visitImage(Image $i): string { return "![{$i->alt}]({$i->url})"; }
}

/** @param list<Doc> $doc */
function render(array $doc, DocVisitor $v): array
{
    return array_map(static fn (Doc $node) => $node->accept($v), $doc);
}

// ---- assertions -------------------------------------------------------------

$doc = [
    new Heading('Welcome', 1),
    new Paragraph('This is a paragraph with five words.'),
    new Image('/x.png', 'a logo'),
];

pdp_assert_eq(['<h1>Welcome</h1>', '<p>This is a paragraph with five words.</p>', '<img src="/x.png" alt="a logo">'],
              render($doc, new HtmlVisitor()), 'html visitor');

pdp_assert_eq(['WELCOME', 'This is a paragraph with five words.', '[image: a logo]'],
              render($doc, new PlainTextVisitor()), 'plain text visitor');

pdp_assert_eq([1, 7, 2], render($doc, new WordCountVisitor()), 'word count per node');

pdp_assert_eq(['# Welcome', 'This is a paragraph with five words.', '![a logo](/x.png)'],
              render($doc, new MarkdownVisitor()), 'markdown visitor added without touching nodes');

// new operations live in new visitor classes; nodes only know `accept`
$ref = new \ReflectionClass(Heading::class);
$methods = array_map(static fn ($m) => $m->getName(), $ref->getMethods());
sort($methods);
pdp_assert_eq(['__construct', 'accept'], $methods, 'Heading carries no per-operation methods');

pdp_done();
