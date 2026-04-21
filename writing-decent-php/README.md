# Writing decent PHP

Readable, maintainable PHP: naming, structure, error handling, dependencies, and habits that keep codebases healthy.

This folder holds reference solutions for the exercises in the Academy course
**Writing decent PHP — style, tests, and maintainable habits**. Each subfolder
matches a section in the course and contains runnable PHP plus a short
walkthrough of *why* the refactor counts as "decent".

## Solution folders

### Chapter 1 — readable naming

- [readable-naming-chapter-1-guided-practice](readable-naming-chapter-1-guided-practice/) —
  three guided refactors covering early returns, hidden dependencies, and
  unnecessary abstraction.
- [readable-naming-quiz](readable-naming-quiz/) — answer key and rationale
  for the 20-question Chapter 1 quiz on the maintainability mindset.

### Chapter 2 — naming variables, functions, classes, and files well

- [naming-things-well-chapter-2-guided-practice](naming-things-well-chapter-2-guided-practice/) —
  three rename-only refactors covering local names + magic numbers, class /
  namespace / file location, and consistent domain language across collections.

### Chapter 3 — functions that do one job

- [functions-that-do-one-job-chapter-3-guided-practice](functions-that-do-one-job-chapter-3-guided-practice/) —
  three structural refactors covering splitting a too-much-work function,
  command/query separation, and removing flag arguments via value objects
  and split methods.

### Chapter 4 — reducing nesting and making code easier to scan

- [reducing-nesting-chapter-4-guided-practice](reducing-nesting-chapter-4-guided-practice/) —
  three nesting refactors covering arrow-shaped guard clauses, dispatch via
  `match`, and keeping the happy path flat as preconditions accumulate.

### Chapter 5 — data shaping with arrays and simple value objects

- [data-shaping-chapter-5-guided-practice](data-shaping-chapter-5-guided-practice/) —
  three data-shaping refactors covering promoting an outgrown array to a real
  value object, designing a brand-new `DateRange` value object well from the
  start, and pushing array → object conversions to a single named boundary.

### Chapter 6 — separating input, work, and output

- [input-work-and-output-boundaries-chapter-6-guided-practice](input-work-and-output-boundaries-chapter-6-guided-practice/) —
  three boundary refactors covering extracting a typed input from a fat
  controller, pulling HTTP out of a use case so the work speaks only in
  domain types, and separating output formatting from the use case via a
  thin presenter.

### Chapter 7 — organising files, folders, and small modules

- [files-folders-and-small-modules-chapter-7-guided-practice](files-folders-and-small-modules-chapter-7-guided-practice/) —
  three structural refactors covering breaking up a `helpers.php` into
  named module folders, flipping a layer-first project into a feature-first
  one, and splitting a too-big `Orders/` module into `OrderPlacement` and
  `OrderFulfilment` with a one-paragraph contract between them.

### Chapter 8 — error handling: exceptions, return values, and boundaries

- [errors-results-and-boundaries-chapter-8-guided-practice](errors-results-and-boundaries-chapter-8-guided-practice/) —
  three error-handling refactors covering replacing raw `\Exception` with
  named domain exceptions translated at the boundary, dropping a
  `catch (\Throwable)` blanket so system failures reach the framework's
  top-level handler, and killing `false`-as-sentinel by splitting one
  repository method into `byEmailOrNull` / `byEmailOrFail`.

### Chapter 9 — writing code that is easy to change

- [change-friendly-code-chapter-9-guided-practice](change-friendly-code-chapter-9-guided-practice/) —
  three change-readiness refactors covering extracting a `Money` value
  object from three duplicated formatters, replacing a `taxFor()`
  if-cascade with a `VatPolicy` registry so adding a region is one new
  map row, and surfacing three hidden dependencies (`time()`, `config()`,
  static `Logger::log`) so the use case becomes testable with no global
  state.

### Chapter 10 — a review of SOLID principles

- [solid-principles-in-practice-chapter-10-guided-practice](solid-principles-in-practice-chapter-10-guided-practice/) —
  three judgement-first SOLID exercises: a pressure review that splits a
  three-job `CustomerService` along SRP/ISP lines, an open/closed
  candidate that picks a `country => rate` data table over per-region
  classes, and a DIP refactor that inverts the Stripe SDK while
  deliberately leaving `DB::` alone (with a paragraph explaining why
  the bar for that second extraction is higher).

### Chapter 11 — single responsibility and cohesion in normal PHP code

- [single-responsibility-and-cohesion-chapter-11-guided-practice](single-responsibility-and-cohesion-chapter-11-guided-practice/) —
  three cohesion-driven refactors: splitting a seven-method `OrderManager`
  into one class per use case (named by the verb, with only the
  collaborators each one actually uses), pulling a wall-clock-dependent
  upgrade-eligibility predicate onto `Subscription` as a deterministic
  method that takes an injected `$now`, and converting a static-only
  `StringHelpers` "fake class" into a `Slug` value object with
  validation at construction (with a paragraph on when namespaced
  functions would have been the better call).

### Chapter 12 — dependency inversion and dependency injection without a framework

- [dependency-inversion-and-di-chapter-12-guided-practice](dependency-inversion-and-di-chapter-12-guided-practice/) —
  three DI-as-muscle-memory refactors: surfacing four hidden globals
  (`new DateTimeImmutable`, `TenantContext`, `Queue`, `Logger`) behind
  named ports with paired production adapters and deterministic test
  doubles, drawing the right interface for `ConvertCurrency` at the
  level of the *question* (`ExchangeRateProvider::rateFor`) rather than
  the *primitive* (HTTP), and assembling the whole graph in a single
  `AppContainer` composition root with a parallel `TestContainer` that
  makes test wiring look like production wiring.

### Chapter 13 — composition over inheritance in app code

- [composition-over-inheritance-chapter-13-guided-practice](composition-over-inheritance-chapter-13-guided-practice/) —
  three composition-as-default-reflex refactors: flattening a
  three-level controller hierarchy by turning each "abstract helper
  layer" into a `JsonResponder` / `RequestAuthenticator` collaborator,
  replacing `$this instanceof CustomerUser` checks with a
  `WelcomeTemplate` strategy + `Audience` enum + `WelcomeTemplateRegistry`
  (deleting the empty marker subclasses entirely), and rewriting
  `LoggingMailer extends SmtpMailer` as a `LoggingMailer implements Mailer`
  decorator that wraps any `Mailer` (with an explicit note on the
  decorator's `sendBulk` design choice).

### Chapter 14 — refactoring a messy feature step by step

- [refactoring-messy-code-safely-chapter-14-guided-practice](refactoring-messy-code-safely-chapter-14-guided-practice/) —
  three progressive exercises operating on the **same** `generateInvoice`
  function: pinning its observable output across five representative
  cases with a characterisation test, renaming for intention without
  touching structure (and proving it by re-running the same test
  byte-for-byte), and extracting one collaborator (`InvoiceTotalsCalculator`)
  with a default-null wiring that keeps existing callers untouched —
  plus a unit test that surfaces a hidden float-imprecision bug the
  characterisation test was silently masking.

### Chapter 15 — code review, comments, documentation, and handover

- [code-review-comments-and-handover-chapter-15-guided-practice](code-review-comments-and-handover-chapter-15-guided-practice/) —
  three exercises on the **artefacts around the code**: cleaning up a
  `PriceCalculator` whose comments are variously wrong, redundant, and
  misplaced (while the one genuinely surprising line — a `sleep(1)` —
  has no *why* at all); writing an ADR using the chapter's template
  (Status / Context / Decision / Consequences / Alternatives considered,
  worked through for Stripe vs PayPal as the card processor); and
  rewriting a terrible PR (title `fix`, empty description, commit
  `fix bug`, diff adding idempotency keys to order creation) into a
  proper title, description, and commit message following the
  Why / What / How / Testing / Risk / Out-of-scope / Links shape.

### Chapter 16 — project checkpoint: maintainable PHP

- [project-checkpoint-maintainable-php-chapter-16-guided-practice](project-checkpoint-maintainable-php-chapter-16-guided-practice/) —
  the whole-course checkpoint, worked on a single end-of-month
  `InvoiceGenerator` in three cumulative steps: **pin behaviour**
  (a characterisation test for the premium-GB-10h / basic-IE-5h
  fixture, plus the skipped-no-sessions and skipped-inactive
  branches, with an honest note about the mail side-effect being
  unobservable until the last step), **name the rules** (extract
  `HourlyRate` and `VatRule` with named constants — the same
  characterisation test stays green), and **collaborate, don't query**
  (replace `DB::`, `mail()`, `file_put_contents`, and every `date()`
  call with five ports — `ClientRepository`, `SessionRepository`,
  `InvoiceRepository`, `InvoiceStore`, `InvoiceNotifier` — a `Clock`,
  and a `BillingPeriod` value object, with in-memory adapters that
  let the unit test assert every side-effect deterministically in
  milliseconds).

← [Half Shell Studios Academy](../README.md)
