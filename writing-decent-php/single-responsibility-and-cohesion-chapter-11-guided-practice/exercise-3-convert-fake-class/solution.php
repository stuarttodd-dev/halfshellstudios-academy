<?php
declare(strict_types=1);

/**
 * Why a `Slug` value object beat namespaced functions here.
 *
 * Both options would kill the "fake class" smell. The deciding factor is
 * whether there's a real domain noun with invariants. There is:
 *
 *   - A slug is a string with rules: lowercase, ASCII-alphanumeric,
 *     hyphen-separated, non-empty after normalisation.
 *   - "Uniqueness against a set of taken slugs" is a question only a
 *     slug can answer — it doesn't apply to arbitrary strings.
 *   - Once you have one, you want to be sure you got it from a place
 *     that obeys the rules, not from `$_POST['slug']` directly. A
 *     value object enforces that at the type level: a `Slug` parameter
 *     can never be an unnormalised user input.
 *
 * Namespaced functions (`Slugs\slugify($s)`, `Slugs\ensureUnique(...)`)
 * would have been the right call if there were *no* invariant — e.g. a
 * trim/uppercase utility. Here there is one, so promote.
 */
final class Slug
{
    private function __construct(public readonly string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Slug must not be empty.');
        }
        if (! preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $value)) {
            throw new InvalidArgumentException("Invalid slug: {$value}");
        }
    }

    public static function fromTitle(string $title): self
    {
        $normalised = strtolower(trim($title));
        $hyphenated = preg_replace('/[^a-z0-9]+/', '-', $normalised) ?? '';
        $trimmed    = trim($hyphenated, '-');

        if ($trimmed === '') {
            throw new InvalidArgumentException("Title \"{$title}\" produces an empty slug.");
        }

        return new self($trimmed);
    }

    /** @param list<Slug> $taken */
    public function madeUniqueAgainst(array $taken): self
    {
        $takenValues = array_map(fn (Slug $s) => $s->value, $taken);

        if (! in_array($this->value, $takenValues, true)) {
            return $this;
        }

        $suffix = 2;
        while (in_array("{$this->value}-{$suffix}", $takenValues, true)) {
            $suffix++;
        }

        return new self("{$this->value}-{$suffix}");
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

/* ---------- driver (identical observable output to starter.php) ---------- */

$titles = ['Hello, World!', 'PHP 8.2 — value objects', 'Hello, World!'];

/** @var list<Slug> $taken */
$taken = [Slug::fromTitle('hello-world')];
$slugs = [];

foreach ($titles as $title) {
    $slug    = Slug::fromTitle($title)->madeUniqueAgainst($taken);
    $taken[] = $slug;
    $slugs[] = (string) $slug;
}

echo "slugs: " . json_encode($slugs) . "\n";
