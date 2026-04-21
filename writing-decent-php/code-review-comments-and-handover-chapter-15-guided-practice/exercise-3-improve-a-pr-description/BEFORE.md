# The PR as shipped (before the rewrite)

```
Title:        fix
Description:  (empty)
Commit:       fix bug
Diff scope:   3 files — adds idempotency keys to order creation
```

## Why this is bad

- **Title** — tells you nothing. "fix" is not a summary; it is the name
  of a feeling the author had. You cannot scan the git log and find
  this change again.
- **Description** — empty. A future reader has three files to read and
  nothing to read them *against*. Why was this needed? What problem
  did it solve? What will break if it is reverted?
- **Commit message** — "fix bug" is the same sin: subject line carries
  zero information, body is missing. `git blame` pointing at this
  commit will cost the next engineer 20 minutes of guesswork.
- **No link to the incident/ticket/ADR** — the context that would
  explain *why* idempotency keys are needed now (rather than last
  year, or next year) lives somewhere else and is not referenced.
- **No testing notes** — a reviewer cannot tell whether this was
  exercised end-to-end, unit-tested only, or hand-tested in staging.
- **No risk note** — idempotency keys touch retryable behaviour on the
  checkout path. If the implementation is wrong, duplicate charges
  become possible. The PR should say out loud how that risk is being
  contained.
