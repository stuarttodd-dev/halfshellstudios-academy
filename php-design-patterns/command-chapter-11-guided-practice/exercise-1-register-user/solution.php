<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Command — immutable value object describing the action and its inputs. */
final class RegisterUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $locale,
    ) {}
}

/** Handler — separate from the command, has its own dependencies. */
final class RegisterUserHandler
{
    public function __construct(private readonly UserService $users) {}

    /** @return object the created user */
    public function __invoke(RegisterUserCommand $command): object
    {
        return $this->users->register($command->email, $command->password, $command->locale);
    }
}

interface UserService { public function register(string $email, string $password, string $locale): object; }
final class FakeUserService implements UserService
{
    public int $nextId = 1;
    public array $created = [];
    public function register(string $email, string $password, string $locale): object
    {
        $u = (object) ['id' => $this->nextId++, 'email' => $email, 'locale' => $locale];
        $this->created[] = $u;
        return $u;
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

    public function register(string $commandClass, callable $handler): void { $this->handlers[$commandClass] = $handler; }
    public function pipe(callable $middleware): void { $this->middleware[] = $middleware; }

    public function dispatch(object $command): mixed
    {
        $core = function (object $cmd) {
            $handler = $this->handlers[$cmd::class] ?? throw new \RuntimeException('No handler for ' . $cmd::class);
            return $handler($cmd);
        };
        // Build the middleware chain inwards so the first piped middleware is OUTERMOST.
        $next = $core;
        foreach (array_reverse($this->middleware) as $mw) {
            $current = $next;
            $next = static fn (object $cmd) => $mw($cmd, $current);
        }
        return $next($command);
    }
}

/** Audit-log middleware: every command flows through. */
final class AuditMiddleware
{
    /** @var list<string> */
    public array $log = [];
    public function __invoke(object $command, callable $next): mixed
    {
        $this->log[] = 'audit:' . $command::class;
        return $next($command);
    }
}

final class RegistrationController
{
    public function __construct(private readonly CommandBus $bus) {}
    public function register(string $email, string $password, string $locale): object
    {
        return $this->bus->dispatch(new RegisterUserCommand($email, $password, $locale));
    }
}

// ---- assertions -------------------------------------------------------------

$users = new FakeUserService();
$audit = new AuditMiddleware();
$bus   = new InMemoryCommandBus();
$bus->register(RegisterUserCommand::class, new RegisterUserHandler($users));
$bus->pipe($audit);

$user = (new RegistrationController($bus))->register('alice@example.com', 'pw', 'en_GB');

pdp_assert_eq(1,                    $user->id,         'controller dispatched and got the user back');
pdp_assert_eq('alice@example.com',  $user->email,      'user has the right email');
pdp_assert_eq(['audit:RegisterUserCommand'], $audit->log, 'audit middleware logged the command');

// Command is immutable data — testable on its own.
$cmd = new RegisterUserCommand('bob@example.com', 'pw', 'en_GB');
pdp_assert_eq('bob@example.com', $cmd->email, 'command holds its inputs');

// Handler is testable with the command directly — no bus, no controller.
$users2 = new FakeUserService();
$user2 = (new RegisterUserHandler($users2))(new RegisterUserCommand('c@example.com', 'pw', 'en_GB'));
pdp_assert_eq('c@example.com', $user2->email, 'handler can be invoked directly');

pdp_done();
