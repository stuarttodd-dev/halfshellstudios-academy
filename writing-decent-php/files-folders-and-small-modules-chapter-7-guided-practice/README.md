# Chapter 7 guided practice — organising files, folders, and small modules

Three exercises to take folder-and-module thinking from idea to habit.
Unlike the earlier chapters, the work here is mostly *structural* — the
behaviour of the code does not change, but the file tree does, and so do
the namespaces.

- **Exercise 1** — break up a `helpers.php` into named module folders
  with one class per concept.
- **Exercise 2** — flip a layer-first project (`Controllers/`,
  `Services/`, `Repositories/`, `Models/`) into a feature-first one
  (`Orders/`, `Invoicing/`, `Admin/`, `Notifications/`) with `Http/` and
  `Persistence/` subfolders where useful.
- **Exercise 3** — split a too-big `Orders/` module into
  `OrderPlacement/` and `OrderFulfilment/`, and write down the contract
  between them.

For exercises 1 and 2 the `before/` and `after/` folders each contain a
runnable `run.php`; their outputs are byte-for-byte identical, which is
the test that the restructure was a true no-op for behaviour. Exercise 3
ships a runnable demo of the *contract* between the two new modules,
plus a `CONTRACT.md` describing it in prose.

## Exercise 1 — break up a `helpers.php`

The starter is a pile of unrelated functions sharing one file. They
have nothing in common except that someone, at some point, didn't know
where else to put them. Re-home each function into a sensibly named
module folder.

### Smells in the starter

- **Six different concepts in one file.** Currency formatting, sending
  email, writing CSV, checking authorisation, audit logging, and tax
  rules are six different topics; merging them into `helpers.php` means
  every consumer has to load all of them just to use one.
- **Global functions.** None of these can be type-hinted, dependency-
  injected, or replaced for testing. `emailReceipt(7)` reaches out to
  a `Mailer` it doesn't declare; `isAdmin(1)` reaches out to a `Db`.
- **No place for the next helper.** When the seventh function gets
  added next month, the only sensible answer is "another line in
  `helpers.php`" — the file becomes a graveyard.

### What the refactor buys

- Six small, single-purpose modules — each one is the obvious place
  for the next thing in that domain to land:
  - `Money/MoneyFormatter`
  - `Tax/VatCalculator`
  - `Notifications/ReceiptMailer`
  - `Csv/CsvExporter`
  - `Access/AdminCheck`
  - `Audit/AuditLog`
- Each module is **explicitly namespaced** (`DecentPhp\Ch7\Ex1\<Module>`)
  so readers can tell at a glance which concept a class belongs to.
- The two side-effecting helpers (`emailReceipt`, `isAdmin`,
  `logAudit`) become **methods on injectable classes** rather than
  global functions, so tests can replace them without monkey-patching.
- `helpers.php` ceases to exist. The next contributor cannot add
  to it.

### Folder layout

```
exercise-1-break-up-a-helpers-file/
├── before/
│   ├── helpers.php               # 6 unrelated global functions
│   ├── stubs.php                 # Mailer, Db, AuditStore (in-memory fakes)
│   └── run.php                   # exercises every helper, prints results
└── after/
    ├── autoload.php              # tiny PSR-4 autoloader
    ├── run.php                   # same calls, same output, organised wiring
    └── src/
        ├── Access/AdminCheck.php
        ├── Audit/AuditLog.php
        ├── Csv/CsvExporter.php
        ├── Money/MoneyFormatter.php
        ├── Notifications/ReceiptMailer.php
        └── Tax/VatCalculator.php
```

### Before

```php
function gbp(int $pence): string { return '£' . number_format($pence / 100, 2); }
function emailReceipt(int $orderId): void { /* uses Mailer */ }
function exportCsv(array $rows, string $path): void { /* writes file */ }
function isAdmin(int $userId): bool { /* DB lookup */ }
function logAudit(string $message, array $context = []): void { /* writes audit log */ }
function calcVat(int $netInPence, string $country): int { /* tax rules */ }
```

### After

```php
namespace DecentPhp\Ch7\Ex1\Money;
final class MoneyFormatter
{
    public static function gbp(int $pence): string
    {
        return '£' . number_format($pence / 100, 2);
    }
}

namespace DecentPhp\Ch7\Ex1\Tax;
final class VatCalculator
{
    public static function calculate(int $netInPence, string $country): int { /* match … */ }
}

namespace DecentPhp\Ch7\Ex1\Notifications;
final class ReceiptMailer { public function emailFor(int $orderId): void { /* … */ } }

namespace DecentPhp\Ch7\Ex1\Csv;
final class CsvExporter { public function export(array $rows, string $path): void { /* … */ } }

namespace DecentPhp\Ch7\Ex1\Access;
final class AdminCheck { public function isAdmin(int $userId): bool { /* … */ } }

namespace DecentPhp\Ch7\Ex1\Audit;
final class AuditLog { public function record(string $message, array $context = []): void { /* … */ } }
```

## Exercise 2 — flip layer-first to feature-first

A layer-first layout puts every controller in `Controllers/`, every
service in `Services/`, and so on. To work on "orders" you have to open
four different folders. A feature-first layout flips the axes so
everything for one feature lives together.

### Smells in the starter

- **Every change touches every layer folder.** Adding a new field to an
  invoice means edits in `Models/`, `Repositories/`, `Services/`, and
  `Controllers/` — four folders, no narrative.
- **No clear home for cross-cutting concerns.** Where does
  `NotificationService` belong? It isn't an order, an invoice, or a
  user; it sits in `Services/` next to things it has nothing to do with.
- **Hard to delete a feature.** "Remove invoicing" is currently a
  hunt-and-peck across every layer folder; in a feature-first layout
  it is `rm -rf src/Invoicing`.

### What the refactor buys

- Top-level folders that match how humans talk about the app:
  `Orders/`, `Invoicing/`, `Admin/`, `Notifications/`.
- `Http/` and `Persistence/` subfolders only where the feature
  actually has those concerns. Notifications has neither; Admin has
  no persistence (yet) — and the absence is informative.
- A new feature is one new top-level folder; a removed feature is one
  `rm -rf`. Git blame for "what changed in Invoicing this quarter"
  becomes a single-folder query.
- Imports tell the story. After the flip, `App\Invoicing\InvoiceService`
  importing `App\Orders\Persistence\OrderRepository` makes the
  cross-feature dependency *visible*; in the layer-first layout that
  same import was buried in a sea of similar lines.

### Folder layout

```
exercise-2-flip-layer-first-to-feature-first/
├── before/
│   └── src/
│       ├── Controllers/{Order,Invoice,Admin}Controller.php
│       ├── Services/{Order,Invoice,Notification}Service.php
│       ├── Repositories/{Order,Invoice}Repository.php
│       └── Models/{Order,Invoice,User}.php
└── after/
    └── src/
        ├── Orders/
        │   ├── Order.php
        │   ├── OrderService.php
        │   ├── Http/OrderController.php
        │   └── Persistence/OrderRepository.php
        ├── Invoicing/
        │   ├── Invoice.php
        │   ├── InvoiceService.php
        │   ├── Http/InvoiceController.php
        │   └── Persistence/InvoiceRepository.php
        ├── Admin/
        │   ├── User.php
        │   └── Http/AdminController.php
        └── Notifications/
            └── NotificationService.php
```

### One observation worth noting

`InvoiceService` still depends on `OrderRepository`. The flip does not
*hide* that — it makes it honest, because the import line now reads
`use App\Orders\Persistence\OrderRepository;` from inside
`App\Invoicing`, which is a clear cross-module call you can see from a
mile away. If those cross-module calls start to multiply, that is a
signal — explored next, in Exercise 3.

## Exercise 3 — split a too-big module

`src/Orders/` has 28 files. Some are clearly about *placing* an order;
others are about *fulfilling* and *shipping* it. Two stories, one
folder. Split it.

### The 28 files, conceptually

| File | New home |
| --- | --- |
| `OrderController.php`         | OrderPlacement |
| `PlaceOrder.php`              | OrderPlacement |
| `PlaceOrderInput.php`         | OrderPlacement |
| `OrderRequest.php`            | OrderPlacement |
| `OrderRepository.php`         | OrderPlacement |
| `Order.php`                   | OrderPlacement |
| `OrderId.php`                 | OrderPlacement |
| `OrderLine.php`               | OrderPlacement |
| `OrderStatus.php`             | OrderPlacement |
| `DraftOrder.php`              | OrderPlacement |
| `CartItem.php`                | OrderPlacement |
| `AddToCartHandler.php`        | OrderPlacement |
| `RemoveFromCartHandler.php`   | OrderPlacement |
| `PriceCalculator.php`         | OrderPlacement |
| `DiscountRule.php`            | OrderPlacement |
| `VatPolicy.php`               | OrderPlacement |
| `PaymentInitiator.php`        | OrderPlacement |
| `PaymentReceipt.php`          | OrderPlacement |
| `OrderConfirmedEvent.php`     | OrderPlacement *(the public surface)* |
| `ShipOrderHandler.php`        | OrderFulfilment |
| `ShippingLabelGenerator.php`  | OrderFulfilment |
| `ShippingCarrier.php`         | OrderFulfilment |
| `TrackingNumber.php`          | OrderFulfilment |
| `DeliveryNotification.php`    | OrderFulfilment |
| `FulfilmentRepository.php`    | OrderFulfilment |
| `FulfilmentStatus.php`        | OrderFulfilment |
| `FulfilmentController.php`    | OrderFulfilment |
| `ReturnsHandler.php`          | OrderFulfilment |

### Folder layout (after the split)

```
exercise-3-split-a-too-big-module/
├── CONTRACT.md                   # one-paragraph contract, the deliverable
└── after/
    ├── autoload.php
    ├── run.php                   # composition root + end-to-end demo
    └── src/
        ├── OrderPlacement/
        │   ├── Domain/OrderId.php
        │   ├── Events/EventBus.php
        │   ├── Events/OrderConfirmedEvent.php
        │   ├── Events/ShippableLine.php
        │   └── UseCases/PlaceOrder.php
        └── OrderFulfilment/
            ├── Domain/FulfilmentOrder.php
            ├── Listeners/StartFulfilmentOnOrderConfirmed.php
            └── Persistence/FulfilmentRepository.php
```

The runnable demo (`after/run.php`) instantiates a tiny in-process
event bus, subscribes Fulfilment's listener to it, hands the bus to
Placement, places an order, and then asserts that a
`FulfilmentOrder` was created in Fulfilment's repository — without
either module importing the other in either direction other than the
one event listener.

### The contract

See `CONTRACT.md` for the one-paragraph deliverable. The short version:
**`OrderPlacement` publishes `OrderConfirmedEvent`. `OrderFulfilment`
subscribes to it. The dependency arrow points one way only — and you
can verify that with `grep`.**

```bash
# Should list exactly one file (the listener)
grep -r 'use App\\OrderPlacement' after/src/OrderFulfilment/

# Should be empty
grep -r 'use App\\OrderFulfilment' after/src/OrderPlacement/
```

If either of those checks ever changes, the modules are no longer
honestly split, and the boundary needs a conversation.

## Running the solutions

Each exercise folder is self-contained and runs with plain PHP — no
Composer, no framework, no database:

```bash
# Exercise 1
cd writing-decent-php/files-folders-and-small-modules-chapter-7-guided-practice/exercise-1-break-up-a-helpers-file
diff <(php before/run.php) <(php after/run.php)   # no output ⇒ behaviour preserved

# Exercise 2
cd ../exercise-2-flip-layer-first-to-feature-first
diff <(php before/run.php) <(php after/run.php)   # no output ⇒ behaviour preserved

# Exercise 3
cd ../exercise-3-split-a-too-big-module
php after/run.php                                  # end-to-end Placement → Event → Fulfilment
grep -r 'use App\\OrderFulfilment' after/src/OrderPlacement/   # should be empty
```
