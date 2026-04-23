<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * States and transitions:
 *
 *   draft -- submit  --> submitted
 *   submitted -- review --> under_review
 *   under_review -- approve --> approved (terminal)
 *   under_review -- reject  --> rejected (terminal)
 *   draft|submitted|under_review -- withdraw --> withdrawn (terminal)
 *   approved|rejected            -- withdraw --> ERROR
 */

final class InvalidApplicationOperation extends \LogicException {}

interface ApplicationState
{
    public function name(): string;
    public function submit(Application $a): void;
    public function review(Application $a): void;
    public function approve(Application $a): void;
    public function reject(Application $a): void;
    public function withdraw(Application $a): void;
}

final class Application
{
    public ApplicationState $state;
    public function __construct(?ApplicationState $initial = null)
    {
        $this->state = $initial ?? new DraftState();
    }
    public function status(): string { return $this->state->name(); }
    public function transitionTo(ApplicationState $s): void { $this->state = $s; }

    public function submit():   void { $this->state->submit($this); }
    public function review():   void { $this->state->review($this); }
    public function approve():  void { $this->state->approve($this); }
    public function reject():   void { $this->state->reject($this); }
    public function withdraw(): void { $this->state->withdraw($this); }
}

abstract class AbstractState implements ApplicationState
{
    public function submit(Application $a): void { throw new InvalidApplicationOperation("cannot submit from {$this->name()}"); }
    public function review(Application $a): void { throw new InvalidApplicationOperation("cannot review from {$this->name()}"); }
    public function approve(Application $a): void { throw new InvalidApplicationOperation("cannot approve from {$this->name()}"); }
    public function reject(Application $a): void { throw new InvalidApplicationOperation("cannot reject from {$this->name()}"); }
    public function withdraw(Application $a): void { throw new InvalidApplicationOperation("cannot withdraw from {$this->name()}"); }
}

final class DraftState extends AbstractState
{
    public function name(): string { return 'draft'; }
    public function submit(Application $a): void { $a->transitionTo(new SubmittedState()); }
    public function withdraw(Application $a): void { $a->transitionTo(new WithdrawnState()); }
}
final class SubmittedState extends AbstractState
{
    public function name(): string { return 'submitted'; }
    public function review(Application $a): void { $a->transitionTo(new UnderReviewState()); }
    public function withdraw(Application $a): void { $a->transitionTo(new WithdrawnState()); }
}
final class UnderReviewState extends AbstractState
{
    public function name(): string { return 'under_review'; }
    public function approve(Application $a): void { $a->transitionTo(new ApprovedState()); }
    public function reject(Application $a): void { $a->transitionTo(new RejectedState()); }
    public function withdraw(Application $a): void { $a->transitionTo(new WithdrawnState()); }
}
final class ApprovedState extends AbstractState  { public function name(): string { return 'approved'; } }
final class RejectedState extends AbstractState  { public function name(): string { return 'rejected'; } }
final class WithdrawnState extends AbstractState { public function name(): string { return 'withdrawn'; } }

// ---- assertions -------------------------------------------------------------

$a = new Application();
pdp_assert_eq('draft', $a->status(), 'starts in draft');

pdp_assert_throws(InvalidApplicationOperation::class, fn () => $a->approve(), 'cannot approve from draft');
pdp_assert_throws(InvalidApplicationOperation::class, fn () => $a->review(),  'cannot review from draft');

$a->submit();
pdp_assert_eq('submitted', $a->status(), 'submit moves to submitted');

$a->review();
pdp_assert_eq('under_review', $a->status(), 'review moves to under_review');

$a->approve();
pdp_assert_eq('approved', $a->status(), 'approve from under_review -> approved');
pdp_assert_throws(InvalidApplicationOperation::class, fn () => $a->withdraw(), 'cannot withdraw once approved');

// rejection path
$b = new Application();
$b->submit(); $b->review(); $b->reject();
pdp_assert_eq('rejected', $b->status(), 'reject path');
pdp_assert_throws(InvalidApplicationOperation::class, fn () => $b->approve(), 'cannot un-reject');

// withdrawal path
$c = new Application();
$c->submit();
$c->withdraw();
pdp_assert_eq('withdrawn', $c->status(), 'withdraw from submitted');

// per-state isolated tests
pdp_assert_eq('approved', (new ApprovedState())->name(), 'state name');
pdp_assert_throws(InvalidApplicationOperation::class, fn () => (new ApprovedState())->withdraw(new Application(new ApprovedState())), 'approved is terminal');

pdp_done();
