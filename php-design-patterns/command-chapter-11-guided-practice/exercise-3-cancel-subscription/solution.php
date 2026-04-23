<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Command: immutable, knows nothing about transport (HTTP / CLI / queue). */
final class CancelSubscriptionCommand
{
    public function __construct(
        public readonly int $subscriptionId,
        public readonly int $userId,
        public readonly string $reason,
    ) {}
}

/** Domain. */
final class Subscription
{
    public string $status = 'active';
    public ?string $cancellationReason = null;
    public function __construct(public readonly int $id, public readonly int $ownerId) {}
    public function cancel(string $reason): void
    {
        if ($this->status === 'cancelled') throw new \LogicException("Already cancelled");
        $this->status = 'cancelled';
        $this->cancellationReason = $reason;
    }
}

interface SubscriptionRepository
{
    public function find(int $id): ?Subscription;
    public function save(Subscription $sub): void;
}
final class InMemorySubscriptionRepository implements SubscriptionRepository
{
    /** @var array<int, Subscription> */
    public array $byId = [];
    public function add(Subscription $sub): void { $this->byId[$sub->id] = $sub; }
    public function find(int $id): ?Subscription { return $this->byId[$id] ?? null; }
    public function save(Subscription $sub): void { $this->byId[$sub->id] = $sub; }
}

/** Handler — bare business logic. */
final class CancelSubscriptionHandler
{
    public function __construct(private readonly SubscriptionRepository $subscriptions) {}
    public function __invoke(CancelSubscriptionCommand $cmd): Subscription
    {
        $sub = $this->subscriptions->find($cmd->subscriptionId) ?? throw new \DomainException('not found');
        $sub->cancel($cmd->reason);
        $this->subscriptions->save($sub);
        return $sub;
    }
}

/** Bus + middleware. */
interface CommandBus { public function dispatch(object $command): mixed; }

final class InMemoryCommandBus implements CommandBus
{
    /** @var array<class-string, callable(object): mixed> */
    private array $handlers = [];
    /** @var list<callable(object, callable(object): mixed): mixed> */
    private array $middleware = [];

    public function register(string $cmdClass, callable $handler): void { $this->handlers[$cmdClass] = $handler; }
    public function pipe(callable $middleware): void { $this->middleware[] = $middleware; }

    public function dispatch(object $command): mixed
    {
        $core = function (object $cmd) {
            $h = $this->handlers[$cmd::class] ?? throw new \RuntimeException('No handler for ' . $cmd::class);
            return $h($cmd);
        };
        $next = $core;
        foreach (array_reverse($this->middleware) as $mw) {
            $current = $next;
            $next = static fn (object $cmd) => $mw($cmd, $current);
        }
        return $next($command);
    }
}

/** Auth middleware: rejects commands the user isn't allowed to issue. */
interface PermissionChecker { public function userCan(int $userId, string $permission): bool; }

final class AuthorizationMiddleware
{
    public function __construct(private readonly PermissionChecker $checker) {}
    public function __invoke(object $cmd, callable $next): mixed
    {
        if ($cmd instanceof CancelSubscriptionCommand && !$this->checker->userCan($cmd->userId, 'subscriptions.cancel')) {
            throw new \DomainException('forbidden');
        }
        return $next($cmd);
    }
}

/** Transactional middleware: begin/commit/rollback. */
final class TransactionalMiddleware
{
    /** @var list<string> */
    public array $log = [];
    public function __invoke(object $cmd, callable $next): mixed
    {
        $this->log[] = 'begin';
        try {
            $result = $next($cmd);
            $this->log[] = 'commit';
            return $result;
        } catch (\Throwable $e) {
            $this->log[] = 'rollback';
            throw $e;
        }
    }
}

/** Audit middleware: append-only log of every dispatched command. */
final class AuditMiddleware
{
    /** @var list<array{class:string,inputs:array<string,mixed>}> */
    public array $log = [];
    public function __invoke(object $cmd, callable $next): mixed
    {
        $this->log[] = ['class' => $cmd::class, 'inputs' => get_object_vars($cmd)];
        return $next($cmd);
    }
}

/** Caller: HTTP controller. CLI / queue worker would build the command identically. */
final class SubscriptionController
{
    public function __construct(private readonly CommandBus $bus) {}
    public function cancel(int $userId, int $subId, string $reason): Subscription
    {
        return $this->bus->dispatch(new CancelSubscriptionCommand($subId, $userId, $reason));
    }
}

// ---- assertions -------------------------------------------------------------

$repo = new InMemorySubscriptionRepository();
$repo->add(new Subscription(id: 1, ownerId: 42));
$repo->add(new Subscription(id: 2, ownerId: 99));

$audit = new AuditMiddleware();
$tx    = new TransactionalMiddleware();
$auth  = new AuthorizationMiddleware(new class implements PermissionChecker {
    public function userCan(int $userId, string $perm): bool { return $userId === 42 && $perm === 'subscriptions.cancel'; }
});

$bus = new InMemoryCommandBus();
$bus->register(CancelSubscriptionCommand::class, new CancelSubscriptionHandler($repo));
$bus->pipe($audit); // outermost
$bus->pipe($auth);
$bus->pipe($tx);

// (1) Happy path through HTTP controller.
$sub = (new SubscriptionController($bus))->cancel(userId: 42, subId: 1, reason: 'too expensive');
pdp_assert_eq('cancelled', $sub->status, 'subscription cancelled');
pdp_assert_eq('too expensive', $sub->cancellationReason, 'reason recorded');
pdp_assert_eq(['begin', 'commit'], $tx->log, 'tx middleware: begin then commit');
pdp_assert_eq([['class' => 'CancelSubscriptionCommand', 'inputs' => ['subscriptionId' => 1, 'userId' => 42, 'reason' => 'too expensive']]],
              $audit->log, 'audit middleware recorded the command');

// (2) Unauthorised dispatch is rejected — same bus, any transport.
$tx->log = []; $audit->log = [];
pdp_assert_throws(
    \DomainException::class,
    fn () => $bus->dispatch(new CancelSubscriptionCommand(subscriptionId: 2, userId: 99, reason: 'cli')),
    'unauthorised user is rejected by auth middleware regardless of caller (HTTP/CLI/queue)',
);
pdp_assert_eq([], $tx->log, 'tx middleware never started — auth ran first and refused');
pdp_assert_eq(1, count($audit->log), 'audit recorded the attempted command (audit is outermost)');

// (3) Failure rolls the tx back.
$tx->log = []; $audit->log = [];
$busBad = new InMemoryCommandBus();
$busBad->register(CancelSubscriptionCommand::class, function (CancelSubscriptionCommand $cmd) {
    throw new \RuntimeException('boom');
});
$busBad->pipe($tx);
pdp_assert_throws(\RuntimeException::class, fn () => $busBad->dispatch(new CancelSubscriptionCommand(1, 42, 'r')), 'handler exception propagates');
pdp_assert_eq(['begin', 'rollback'], $tx->log, 'tx middleware rolled back on exception');

pdp_done();
