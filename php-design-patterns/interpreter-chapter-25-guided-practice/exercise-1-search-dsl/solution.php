<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface SearchExpr
{
    /** @param array<string,mixed> $article */
    public function matches(array $article): bool;
}

final class TagExpr implements SearchExpr
{
    public function __construct(public readonly string $tag) {}
    public function matches(array $article): bool
    {
        $tags = $article['tags'] ?? [];
        return is_array($tags) && in_array($this->tag, $tags, true);
    }
}

final class LevelExpr implements SearchExpr
{
    public function __construct(public readonly string $level) {}
    public function matches(array $article): bool
    {
        return ($article['level'] ?? null) === $this->level;
    }
}

final class AndExpr implements SearchExpr
{
    public function __construct(public readonly SearchExpr $left, public readonly SearchExpr $right) {}
    public function matches(array $article): bool { return $this->left->matches($article) && $this->right->matches($article); }
}

final class OrExpr implements SearchExpr
{
    public function __construct(public readonly SearchExpr $left, public readonly SearchExpr $right) {}
    public function matches(array $article): bool { return $this->left->matches($article) || $this->right->matches($article); }
}

/**
 * Tiny recursive-descent parser for:
 *   expr   := term ( OR term )*
 *   term   := factor ( AND factor )*
 *   factor := atom | '(' expr ')'
 *   atom   := KEY ':' VALUE
 */
final class SearchQueryParser
{
    /** @var list<string> */
    private array $tokens = [];
    private int $pos = 0;

    public function parse(string $query): SearchExpr
    {
        $this->tokens = $this->tokenise($query);
        $this->pos = 0;
        $expr = $this->parseExpr();
        if ($this->pos !== count($this->tokens)) {
            throw new \RuntimeException('unexpected trailing tokens');
        }
        return $expr;
    }

    /** @return list<string> */
    private function tokenise(string $q): array
    {
        $padded = preg_replace('/([()])/', ' $1 ', $q) ?? $q;
        $parts = preg_split('/\s+/', trim($padded));
        return array_values(array_filter($parts ?: [], static fn (string $t) => $t !== ''));
    }

    private function parseExpr(): SearchExpr
    {
        $left = $this->parseTerm();
        while ($this->peek() === 'OR') {
            $this->consume();
            $right = $this->parseTerm();
            $left = new OrExpr($left, $right);
        }
        return $left;
    }

    private function parseTerm(): SearchExpr
    {
        $left = $this->parseFactor();
        while ($this->peek() === 'AND') {
            $this->consume();
            $right = $this->parseFactor();
            $left = new AndExpr($left, $right);
        }
        return $left;
    }

    private function parseFactor(): SearchExpr
    {
        if ($this->peek() === '(') {
            $this->consume();
            $expr = $this->parseExpr();
            if ($this->peek() !== ')') throw new \RuntimeException('expected )');
            $this->consume();
            return $expr;
        }
        $atom = $this->consume();
        [$key, $value] = explode(':', $atom, 2);
        return match ($key) {
            'tag'   => new TagExpr($value),
            'level' => new LevelExpr($value),
            default => throw new \RuntimeException("unknown key {$key}"),
        };
    }

    private function peek(): ?string { return $this->tokens[$this->pos] ?? null; }
    private function consume(): string { return $this->tokens[$this->pos++]; }
}

// ---- assertions -------------------------------------------------------------

$articles = [
    ['title' => 'php basics',          'tags' => ['php'],         'level' => 'beginner'],
    ['title' => 'php patterns',        'tags' => ['php', 'oop'],  'level' => 'intermediate'],
    ['title' => 'concurrency in go',   'tags' => ['go'],          'level' => 'intermediate'],
    ['title' => 'php advanced topics', 'tags' => ['php'],         'level' => 'advanced'],
];

$tree = (new SearchQueryParser())->parse('tag:php AND ( level:beginner OR level:intermediate )');

$matched = array_map(static fn ($a) => $a['title'], array_filter($articles, static fn ($a) => $tree->matches($a)));
pdp_assert_eq(['php basics', 'php patterns'], array_values($matched), 'parser + interpreter find expected articles');

// AST built by hand also works
$byHand = new AndExpr(new TagExpr('php'), new OrExpr(new LevelExpr('beginner'), new LevelExpr('intermediate')));
$byHandTitles = array_values(array_map(static fn ($a) => $a['title'], array_filter($articles, static fn ($a) => $byHand->matches($a))));
pdp_assert_eq(array_values($matched), $byHandTitles, 'parser AST equivalent to hand-built AST');

// per-leaf
pdp_assert_eq(true,  (new TagExpr('php'))->matches($articles[0]), 'tag leaf positive');
pdp_assert_eq(false, (new TagExpr('php'))->matches($articles[2]), 'tag leaf negative');
pdp_assert_eq(true,  (new LevelExpr('intermediate'))->matches($articles[1]), 'level leaf positive');

pdp_done();
