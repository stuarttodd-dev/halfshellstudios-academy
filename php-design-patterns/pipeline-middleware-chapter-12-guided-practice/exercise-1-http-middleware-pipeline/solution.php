<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class Request
{
    /** @param array<string,string> $headers */
    public function __construct(
        public readonly array $headers = [],
        public readonly string $body = '',
        /** @var array<string,mixed> */
        public array $attributes = [],
    ) {}
    public function header(string $name): ?string { return $this->headers[$name] ?? null; }
    public function body(): string { return $this->body; }
}

final class Response
{
    public function __construct(
        public readonly mixed $body,
        public readonly int $status = 200,
    ) {}
}

interface Middleware
{
    public function handle(Request $request, callable $next): Response;
}

interface Handler
{
    public function handle(Request $request): Response;
}

/** Composes [m1, m2, m3] around a core $handler so m1 runs first. */
final class Pipeline implements Handler
{
    /** @param list<Middleware> $middleware */
    public function __construct(
        private readonly array $middleware,
        private readonly Handler $core,
    ) {}

    public function handle(Request $request): Response
    {
        $next = fn (Request $r): Response => $this->core->handle($r);
        foreach (array_reverse($this->middleware) as $mw) {
            $current = $next;
            $next = fn (Request $r): Response => $mw->handle($r, $current);
        }
        return $next($request);
    }
}

final class ApiKeyAuthMiddleware implements Middleware
{
    public function __construct(private readonly string $expected) {}
    public function handle(Request $request, callable $next): Response
    {
        if ($request->header('X-Api-Key') !== $this->expected) return new Response('unauthorised', 401);
        return $next($request);
    }
}

final class JsonPayloadMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $decoded = json_decode($request->body, true);
        if (!is_array($decoded)) return new Response('invalid json', 400);
        $request->attributes['payload'] = $decoded;
        return $next($request);
    }
}

interface UserRepository { public function create(string $email): object; }
final class InMemoryUsers implements UserRepository
{
    private int $next = 1;
    /** @var array<int,object> */
    public array $byId = [];
    public function create(string $email): object
    {
        $u = (object) ['id' => $this->next++, 'email' => $email];
        $this->byId[$u->id] = $u;
        return $u;
    }
}

/** Core controller — only the real job. */
final class CreateUserHandler implements Handler
{
    public function __construct(private readonly UserRepository $users) {}
    public function handle(Request $request): Response
    {
        $payload = $request->attributes['payload'];
        if (!isset($payload['email'])) return new Response('email required', 400);
        return new Response(['id' => $this->users->create($payload['email'])->id], 201);
    }
}

// ---- wiring (composition root) ---------------------------------------------

$users = new InMemoryUsers();
$pipeline = new Pipeline(
    middleware: [
        new ApiKeyAuthMiddleware('secret'),
        new JsonPayloadMiddleware(),
    ],
    core: new CreateUserHandler($users),
);

// ---- assertions -------------------------------------------------------------

$bad = $pipeline->handle(new Request(headers: [], body: '{"email":"a@b"}'));
pdp_assert_eq(401, $bad->status, 'no api key -> auth middleware short-circuits');

$badJson = $pipeline->handle(new Request(headers: ['X-Api-Key' => 'secret'], body: 'not json'));
pdp_assert_eq(400, $badJson->status, 'bad json -> json middleware short-circuits');

$missing = $pipeline->handle(new Request(headers: ['X-Api-Key' => 'secret'], body: '{}'));
pdp_assert_eq(400, $missing->status, 'core handler validates domain rules');

$ok = $pipeline->handle(new Request(headers: ['X-Api-Key' => 'secret'], body: '{"email":"a@b.test"}'));
pdp_assert_eq(201, $ok->status, 'happy path returns 201');
pdp_assert_eq(['id' => 1], $ok->body, 'returns created user id');
pdp_assert_eq('a@b.test', $users->byId[1]->email, 'user persisted');

pdp_done();
