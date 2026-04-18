# Zero to PHP

This folder in **this repository** holds sample PHP that goes with the Academy track on **namespaces, autoloading, and Composer** — including lessons such as `composer-json-autoload`, **[chapter-7-calculator](https://github.com/stuarttodd-dev/halfshellstudios-academy/tree/main/zero-to-php/chapter-7-calculator)** (Chapter 7 practice: calculator service + entry script), and **`vendor-autoload-php`** (what `vendor/autoload.php` is for).

## What Composer autoload does

In `composer.json`, the `autoload` section (often **PSR-4**) tells Composer how your namespace prefixes map to folders on disk. When you run **`composer install`** or **`composer dump-autoload`**, Composer generates **`vendor/autoload.php`**. Your application includes that single file once; it registers Composer’s autoloader with PHP so classes are loaded automatically when you reference them (for example with `use App\SomeClass`) instead of adding a `require` for every class file by hand.

The **[composer-json-autoload](composer-json-autoload/)** directory is a minimal layout: `App\` → `src/`, and an entry script that `require`s `vendor/autoload.php`.

The **[chapter-7-calculator](https://github.com/stuarttodd-dev/halfshellstudios-academy/tree/main/zero-to-php/chapter-7-calculator)** directory is the Chapter 7 practice exercise: `App\CalculatorService` plus `public/index.php` driving add, subtract, multiply, and divide with Composer autoload.

**[Chapter 7 recap](chapter-7-recap.md)** — step-by-step walkthrough of that solution. Sample code on GitHub: <https://github.com/stuarttodd-dev/halfshellstudios-academy/tree/main/zero-to-php/chapter-7-calculator>

## Additional solution folders

### Security / APIs / CLI packaging

- [openssl-encrypt-openssl-decrypt-intro](openssl-encrypt-openssl-decrypt-intro/)
- [move-uploaded-file-recap](move-uploaded-file-recap/)
- [stream-context-create-for-http](stream-context-create-for-http/)
- [get-headers](get-headers/)
- [curl-init-curl-setopt-curl-exec-if-ext-curl](curl-init-curl-setopt-curl-exec-if-ext-curl/)
- [phar-mapphar-awareness](phar-mapphar-awareness/)

### Chapter 16 (all lessons)

#### Build 1 (CLI)

- [cli-project-setup-and-command-entrypoint](cli-project-setup-and-command-entrypoint/)
- [cli-args-and-input-validation](cli-args-and-input-validation/)
- [cli-persistent-storage-with-json](cli-persistent-storage-with-json/)
- [cli-list-and-detail-commands](cli-list-and-detail-commands/)
- [cli-update-delete-and-error-handling](cli-update-delete-and-error-handling/)
- [cli-capstone-and-solution-link](cli-capstone-and-solution-link/)

#### Build 2 (Session basket)

- [basket-project-setup-and-session-bootstrap](basket-project-setup-and-session-bootstrap/)
- [basket-add-items-and-session-structure](basket-add-items-and-session-structure/)
- [basket-update-quantities-and-remove-items](basket-update-quantities-and-remove-items/)
- [basket-totals-and-price-formatting](basket-totals-and-price-formatting/)
- [basket-checkout-flow-and-validation](basket-checkout-flow-and-validation/)
- [basket-capstone-and-solution-link](basket-capstone-and-solution-link/)

#### Build 3 (CRUD app)

- [crud-routing-and-list-screen](crud-routing-and-list-screen/)
- [crud-create-form-and-server-validation](crud-create-form-and-server-validation/)
- [crud-edit-flow-and-prefilled-form](crud-edit-flow-and-prefilled-form/)
- [crud-delete-flow-and-confirmation](crud-delete-flow-and-confirmation/)
- [crud-persistence-layer-and-query-safety](crud-persistence-layer-and-query-safety/)
- [crud-capstone-and-solution-link](crud-capstone-and-solution-link/)

#### Build 4 (JSON API)

- [api-routing-and-health-endpoint](api-routing-and-health-endpoint/)
- [api-get-list-endpoint-and-contract-shape](api-get-list-endpoint-and-contract-shape/)
- [api-post-create-with-validation](api-post-create-with-validation/)
- [api-json-file-persistence-and-ids](api-json-file-persistence-and-ids/)
- [api-error-shape-status-codes-and-smoke-tests](api-error-shape-status-codes-and-smoke-tests/)
- [api-capstone-and-solution-link](api-capstone-and-solution-link/)

← [Half Shell Studios Academy](../README.md)
