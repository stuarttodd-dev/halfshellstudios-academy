# Chapter 13 — Exercise: refactor a fat “contact sales” controller

**Course page:** [Refactor toward services, DTOs, and seams](http://127.0.0.1:38080/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app)

## Target shape

1. `StoreLeadRequest` — validation + `authorize`.
2. `CreateLeadData` — readonly DTO from validated input.
3. `CreateLead` action — constructor-injected `CrmClient`, creates `Lead`, dispatches `SendLeadNotificationJob`, fires `LeadSubmitted`.
4. `App\Contracts\CrmClient` + implementation using `Http::` with `config('services.crm')` base URL (merge keys from `files/config/SERVICES_CRM_SNIPPET.txt`).
5. `LeadController@store` — three to six lines: validate via request, DTO, action, return response.

**Bind** `CrmClient` in a service provider. Tests: unit test on `CreateLead` with a fake CRM; feature test with `Http::fake()`, `Queue::fake()`, `Event::fake()`.

## Files in `files/`

Core snippets for DTO, action, request, contract, job, event, and a thin controller. Merge into your app’s namespaces and add a `leads` table migration to match the `Lead` model fields you use.

## Course link

[exercise-refactor-app](http://127.0.0.1:38080/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app)
