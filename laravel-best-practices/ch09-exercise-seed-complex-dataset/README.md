# Chapter 9 — Exercise: seed a complex demo dataset

**Course page:** [Build a multi-tenant demo with factories and seeders](http://127.0.0.1:38080/learn/sections/chapter-9-factories-seeders-transactions/exercise-seed-complex-dataset)

## Reference layout

The course wants: `RoleSeeder` (idempotent), factories with meaningful states, `DatabaseSeeder` as a table of contents, a `DemoContentSeeder` building three orgs with members, projects, tasks, tags — wrapped in `DB::transaction` where appropriate, and a volume constant.

## Files in `files/`

- `database/seeders/RoleSeeder.php`
- `database/seeders/DemoContentSeeder.php`
- `database/seeders/DatabaseSeeder.php` (example `call` order + environment guard)
- `database/factories/OrganisationFactory.php`, `ProjectFactory.php`, `TaskFactory.php`, `TagFactory.php` (minimal; expand to match your migrations)

You must align model and table names with your own migrations (`organisations`, `organisation_user`, etc.). The seeders show the *shape* the lesson marks as “good”: small methods, `firstOrCreate` for reference data, and a top-of-file `DEMO_TASKS_PER_PROJECT` constant for the volume knob.
