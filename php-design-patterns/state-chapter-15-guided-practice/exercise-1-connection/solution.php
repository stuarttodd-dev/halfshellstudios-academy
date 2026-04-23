<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class InvalidConnectionOperation extends \LogicException {}

interface ConnectionState
{
    public function name(): string;
    public function open(Connection $c): void;
    public function send(Connection $c, string $msg): void;
    public function close(Connection $c): void;
}

final class Connection
{
    /** @var list<string> */
    public array $sent = [];
    public ConnectionState $state;

    public function __construct(?ConnectionState $initial = null)
    {
        $this->state = $initial ?? new ClosedState();
    }

    public function status(): string { return $this->state->name(); }

    public function transitionTo(ConnectionState $s): void { $this->state = $s; }

    public function open(): void  { $this->state->open($this); }
    public function send(string $msg): void { $this->state->send($this, $msg); }
    public function close(): void { $this->state->close($this); }
}

final class ClosedState implements ConnectionState
{
    public function name(): string { return 'closed'; }
    public function open(Connection $c): void { $c->transitionTo(new ConnectingState()); }
    public function send(Connection $c, string $m): void { throw new InvalidConnectionOperation('cannot send while closed'); }
    public function close(Connection $c): void { throw new InvalidConnectionOperation('already closed'); }
}

final class ConnectingState implements ConnectionState
{
    public function name(): string { return 'connecting'; }
    public function open(Connection $c): void { throw new InvalidConnectionOperation('already connecting'); }
    public function send(Connection $c, string $m): void { throw new InvalidConnectionOperation('not yet open'); }
    public function close(Connection $c): void { $c->transitionTo(new ClosedState()); }
    /** Test/runtime hook used when the handshake finishes. */
    public function established(Connection $c): void { $c->transitionTo(new OpenState()); }
}

final class OpenState implements ConnectionState
{
    public function name(): string { return 'open'; }
    public function open(Connection $c): void { throw new InvalidConnectionOperation('already open'); }
    public function send(Connection $c, string $m): void { $c->sent[] = $m; }
    public function close(Connection $c): void { $c->transitionTo(new ClosedState()); }
}

// ---- assertions -------------------------------------------------------------

$c = new Connection();
pdp_assert_eq('closed', $c->status(), 'starts closed');
pdp_assert_throws(InvalidConnectionOperation::class, fn () => $c->send('x'), 'cannot send when closed');
pdp_assert_throws(InvalidConnectionOperation::class, fn () => $c->close(),   'cannot close when closed');

$c->open();
pdp_assert_eq('connecting', $c->status(), 'open() from closed -> connecting');
pdp_assert_throws(InvalidConnectionOperation::class, fn () => $c->send('x'), 'cannot send while connecting');

(new ConnectingState())->established($c); // simulate handshake completing
// Note: state replaced by previous step, so we transition manually for the demo
$c->transitionTo(new OpenState());
pdp_assert_eq('open', $c->status(), 'transitioned to open');

$c->send('hi');
$c->send('there');
pdp_assert_eq(['hi', 'there'], $c->sent, 'sends recorded when open');
pdp_assert_throws(InvalidConnectionOperation::class, fn () => $c->open(), 'cannot open when already open');

$c->close();
pdp_assert_eq('closed', $c->status(), 'closed from open');
pdp_assert_throws(InvalidConnectionOperation::class, fn () => $c->close(), 'cannot close again');

// each state class is independently testable
pdp_assert_eq('closed', (new ClosedState())->name(), 'state objects have meaningful names');
pdp_assert_eq('connecting', (new ConnectingState())->name(), '');
pdp_assert_eq('open', (new OpenState())->name(), '');

pdp_done();
