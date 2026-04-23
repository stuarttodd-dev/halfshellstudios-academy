<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Immutable event — pure data describing what happened. */
final class UserRegistered
{
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $source,
    ) {}
}

interface EventDispatcher
{
    public function dispatch(object $event): void;
}

/** Tiny dispatcher: keyed by event class name, fans out to subscribers. */
final class InMemoryEventDispatcher implements EventDispatcher
{
    /** @var array<class-string, list<callable(object): void>> */
    private array $subscribers = [];

    public function subscribe(string $eventClass, callable $subscriber): void
    {
        $this->subscribers[$eventClass][] = $subscriber;
    }

    public function dispatch(object $event): void
    {
        foreach ($this->subscribers[$event::class] ?? [] as $subscriber) {
            $subscriber($event);
        }
    }
}

/** Stand-ins for the real-world subscribers' dependencies. */
interface Mailer { public function send(string $to, string $subject): void; }
interface AnalyticsClient { public function track(string $event, array $context): void; }
interface NewsletterClient { public function subscribe(string $email, string $list): void; }

final class RecordingMailer implements Mailer { public array $sent = []; public function send(string $to, string $subject): void { $this->sent[] = compact('to', 'subject'); } }
final class RecordingAnalytics implements AnalyticsClient { public array $events = []; public function track(string $event, array $context): void { $this->events[] = compact('event', 'context'); } }
final class RecordingNewsletter implements NewsletterClient { public array $subs = []; public function subscribe(string $email, string $list): void { $this->subs[] = compact('email', 'list'); } }

/** One subscriber per reaction. Each owns ONLY its own dependency. */
final class SendVerifyEmailOnRegistration
{
    public function __construct(private readonly Mailer $mailer) {}
    public function __invoke(UserRegistered $event): void
    {
        $this->mailer->send($event->email, 'Verify your email');
    }
}

final class TrackRegistrationOnRegistration
{
    public function __construct(private readonly AnalyticsClient $analytics) {}
    public function __invoke(UserRegistered $event): void
    {
        $this->analytics->track('user.registered', ['source' => $event->source, 'user_id' => $event->userId]);
    }
}

final class SubscribeToNewsletterOnRegistration
{
    public function __construct(private readonly NewsletterClient $newsletter) {}
    public function __invoke(UserRegistered $event): void
    {
        $this->newsletter->subscribe($event->email, 'general');
    }
}

interface UserRepository { public function create(string $email, string $password): object; }
final class FakeUserRepository implements UserRepository
{
    public int $nextId = 1;
    public function create(string $email, string $password): object
    {
        return (object) ['id' => $this->nextId++, 'email' => $email];
    }
}

/** Controller depends only on what it needs for its real job + the dispatcher. */
final class RegistrationController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EventDispatcher $events,
    ) {}

    public function register(string $email, string $password, string $source): object
    {
        $user = $this->users->create($email, $password);
        $this->events->dispatch(new UserRegistered($user->id, $email, $source));
        return $user;
    }
}

// ---- assertions -------------------------------------------------------------

$mailer    = new RecordingMailer();
$analytics = new RecordingAnalytics();
$newsletter = new RecordingNewsletter();

$dispatcher = new InMemoryEventDispatcher();
$dispatcher->subscribe(UserRegistered::class, new SendVerifyEmailOnRegistration($mailer));
$dispatcher->subscribe(UserRegistered::class, new TrackRegistrationOnRegistration($analytics));
$dispatcher->subscribe(UserRegistered::class, new SubscribeToNewsletterOnRegistration($newsletter));

$controller = new RegistrationController(new FakeUserRepository(), $dispatcher);
$user = $controller->register('alice@example.com', 'pw', 'homepage');

pdp_assert_eq(1, $user->id, 'controller created the user');
pdp_assert_eq([['to' => 'alice@example.com', 'subject' => 'Verify your email']], $mailer->sent, 'verify email sent');
pdp_assert_eq([['event' => 'user.registered', 'context' => ['source' => 'homepage', 'user_id' => 1]]], $analytics->events, 'analytics tracked');
pdp_assert_eq([['email' => 'alice@example.com', 'list' => 'general']], $newsletter->subs, 'newsletter subscribed');

// Subscribers are testable in isolation — no controller, no dispatcher needed.
$mailer = new RecordingMailer();
(new SendVerifyEmailOnRegistration($mailer))(new UserRegistered(7, 'bob@example.com', 'cli'));
pdp_assert_eq(1, count($mailer->sent), 'subscriber tested in isolation with the event directly');

// Originator is testable with NO subscribers — it just dispatches.
$dispatched = [];
$justRecord = new class implements EventDispatcher {
    public array $events = [];
    public function dispatch(object $event): void { $this->events[] = $event; }
};
(new RegistrationController(new FakeUserRepository(), $justRecord))->register('x@example.com', 'pw', 'web');
pdp_assert_eq(1, count($justRecord->events), 'controller dispatches exactly one event');
pdp_assert_true($justRecord->events[0] instanceof UserRegistered, 'controller dispatches a UserRegistered');

pdp_done();
