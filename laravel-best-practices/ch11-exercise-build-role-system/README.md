# Chapter 11 — Exercise: org-scoped authorisation (policies)

**Course page:** [Build a B2B-style role and policy layer](http://127.0.0.1:38080/learn/sections/chapter-11-authorisation/exercise-build-role-system)

## Files

- Migrations: `organisations`, add `organisation_id` + `role` on `users`, `projects` with `organisation_id`.
- `app/Models/Organisation`, `Project`, and an updated `User` with `ProjectPolicy`.
- `files/app/Http/Controllers/ProjectController.php` with `$this->authorize` calls.
- `files/tests/Feature/ProjectPolicyTest.php` — cross-tenant 403/404, role gates.

## Status codes

Document whether cross-org `show` returns 403 or 404 and test for that, as the lesson requires.

## Apply

1. Run migrations, then copy models and policy.
2. Register the policy in `AppServiceProvider` or `AuthServiceProvider` (`Project::class => ProjectPolicy::class`).
3. `php artisan test --filter=ProjectPolicyTest`
