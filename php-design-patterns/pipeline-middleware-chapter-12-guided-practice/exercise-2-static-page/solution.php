<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — adding a custom Pipeline here is overkill.
 *
 * Original:
 *
 *     Route::get('/about', fn () => view('about'));
 *
 * The framework already runs its global HTTP middleware (CSRF, sessions,
 * cookies, error handling) around every route. A static page handler
 * answering with a rendered template needs nothing else. Building a
 * second, hand-rolled pipeline would mean:
 *   - more code, more wiring, more places to read,
 *   - a second mental model for HTTP request handling in the project,
 *   - zero new behaviour (the framework already does everything we need).
 *
 * The right move is to leave the closure exactly where it is. If a real
 * cross-cutting concern shows up later (auth, rate limiting, A/B), use
 * the framework's middleware mechanism rather than inventing a second one.
 *
 * Below is a small simulation that proves the point: even without any
 * pipeline at all, a one-line handler does the job.
 */

final class StaticPageHandler
{
    public function __construct(private readonly string $body) {}
    public function __invoke(): string { return $this->body; }
}

$about = new StaticPageHandler('<h1>About</h1>');
pdp_assert_eq('<h1>About</h1>', $about(), 'plain handler is sufficient');

pdp_done('Pipeline was the wrong answer for a static page — see the comment block.');
