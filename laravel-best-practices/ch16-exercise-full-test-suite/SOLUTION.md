# Chapter 16 — Exercise: test suite and CI (reference write-up)

**Course page:** [Full test suite and one CI run](http://127.0.0.1:38080/learn/sections/chapter-16-testing-laravel/exercise-full-test-suite)

The exercise wants tests **you** add to **your** project, plus a green/red proof and either CI config or a script. This document is a **filled-in example** you can compare against.

---

## 1. Tests added (name + one sentence)

| Test | Fails if… |
| --- | --- |
| `test_guest_cannot_delete_other_users_document` | A guest or wrong user can `DELETE` a file row they do not own. |
| `test_owner_can_upload_and_row_exists` | Happy path: `201`, `assertDatabaseHas` on `documents` for the uploader. |
| `test_dispatch_sends_welcome_email_job` | `Mail::fake()`: a mailable is queued or sent when the route completes; removing `Mail::` in the action breaks the test. |

## 2. Command used locally

```bash
php artisan test
php artisan test --parallel   # only if the suite is parallel-safe (no `RefreshDatabase` collisions you care about; see the course on 16.13)
```

## 3. Red–green check

- Change one `assertStatus(201)` to `200` in a new test; run `php artisan test` and confirm **failure**.
- Revert; confirm **green**. Keep the habit: every new assertion should fail if the behaviour regresses.

## 4. CI shape (example: GitHub Actions)

A minimal job, in your own words for the hand-in:

1. `actions/checkout`
2. `shivammathur/setup-php` with `extensions: dom, curl, libxml, mbstring, zip, pdo, mysql` (match your `phpunit.xml` DB)
3. `composer install` (or `--no-dev` for deploy pipelines; for tests, dev deps on)
4. `cp .env.example .env` and set `DB_DATABASE` to an **in-memory** SQLite or a service MySQL, matching `phpunit.xml`
5. `php artisan test` — exit code 0 only on green; CI fails the step otherwise.

```yaml
# .github/workflows/tests.yml (sketch; verify against your stack)
on: [push, pull_request]
jobs:
  php-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none
      - run: composer install
      - run: cp .env.example .env && php artisan key:generate
      - run: touch database/database.sqlite
      - run: echo 'DB_CONNECTION=sqlite' >> .env && echo 'DB_DATABASE='$(pwd)/database/database.sqlite >> .env
      - run: php artisan migrate --force
      - run: php artisan test
```

**Done when (from the course):** someone else on the project runs the same `php artisan test` and sees green, or a clear note explains env differences (e.g. PCOV for coverage, optional for this hand-in).
