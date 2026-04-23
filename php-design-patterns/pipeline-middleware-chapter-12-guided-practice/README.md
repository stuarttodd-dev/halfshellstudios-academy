# Chapter 12 — Pipeline / Middleware (guided practice)

Pipeline / Middleware composes small, single-concern steps around a core
handler so each one can short-circuit, transform input/output, or wrap
the rest of the chain. The trap is reaching for it when one line of
framework wiring already does the job.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — HTTP middleware | Controller doing auth + parsing + the work | **Pipeline fits** — `ApiKeyAuthMiddleware`, `JsonPayloadMiddleware`, controller reduced to its real job |
| 2 — Static page | A one-line `Route::get('/about', ...)` | **Trap.** Framework middleware already covers it |
| 3 — Image processing | Inline ifs + linear transforms in one method | **Pipeline fits** — `ImagePipeline` with steps reused for thumbnails and full-size |

---

## Exercise 1 — HTTP middleware pipeline

### Before

```php
public function handle(Request $request): Response
{
    if ($request->header('X-Api-Key') !== 'secret') return new Response('no', 401);
    $payload = json_decode($request->body(), true);
    if (! is_array($payload) || ! isset($payload['email'])) return new Response('bad', 400);
    return new Response(['id' => $this->users->create($payload['email'])->id]);
}
```

### After

```php
interface Middleware { public function handle(Request $r, callable $next): Response; }
interface Handler    { public function handle(Request $r): Response; }

final class Pipeline implements Handler { /* composes [m1, m2] around $core */ }

$pipeline = new Pipeline(
    middleware: [new ApiKeyAuthMiddleware('secret'), new JsonPayloadMiddleware()],
    core: new CreateUserHandler($users),
);
```

The controller is now one method that knows about its real job.
Auth and JSON parsing each live in one class with one test.

---

## Exercise 2 — Static page (the trap)

### Verdict — Pipeline is the wrong answer

`Route::get('/about', fn () => view('about'))` already runs through the
framework's global HTTP middleware (CSRF, sessions, cookies, error
handling). A custom pipeline would:

- duplicate behaviour the framework already provides;
- introduce a second mental model for HTTP request handling;
- add code (and failure points) for zero new behaviour.

Save Pipeline for places where you genuinely want to compose your own
ordered, single-concern wrappers around a core handler.

---

## Exercise 3 — Image processing pipeline

### Before

```php
public function import(Image $image): Image
{
    if ($image->width() > 4000) $image = $image->resize(4000);
    $image = $image->stripExif();
    if ($this->config->watermarkEnabled()) $image = $image->watermark();
    return $image->compress(85);
}
```

### After

```php
interface ImageStep { public function process(Image $i, callable $next): Image; }
final class ImagePipeline { /* runs [steps] left-to-right around the core identity */ }

$fullSize  = new ImagePipeline([new ResizeMax(4000), new StripExif(), new Watermark(), new Compress(85)]);
$thumbnail = new ImagePipeline([new ResizeMax(400),  new StripExif(),                 new Compress(70)]);
```

The same step classes feed two pipelines. Adding a new step (e.g.
`Sharpen`) is one new class, no edits to existing ones.

---

## Chapter rubric

For each non-trap exercise:

- a middleware contract (`handle(input, next): output`)
- one middleware class per concern
- a runner that composes middlewares around a core handler
- wiring assembled at the composition root from reusable parts

For the trap: explain why the framework's existing middleware suffices.

---

## How to run

```bash
cd php-design-patterns/pipeline-middleware-chapter-12-guided-practice
php exercise-1-http-middleware-pipeline/solution.php
php exercise-2-static-page/solution.php
php exercise-3-image-pipeline/solution.php
```
