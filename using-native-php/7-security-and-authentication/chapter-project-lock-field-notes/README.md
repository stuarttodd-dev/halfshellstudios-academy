# Chapter 7 — Field Notes: biometric app lock

**Capstone:** [All chapters](../../CAPSTONE.md) · [← Chapter 6](../../6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/) · **Chapter 7** · [Chapter 8 →](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/)

**Course:** [Chapter project: lock Field Notes](https://docker.learnio.dev/learn/sections/chapter-security-and-authentication/chapter-project-lock-field-notes)

**Previous:** [Chapter 6 — Offline CRUD](../../6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/) · **Next:** [Chapter 8 — Deep link + sync stub](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/)

**Built on:** [Chapter 6 — Offline CRUD](../../6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/) · **You add:** biometric lock middleware, Livewire unlock gate, Settings toggle.

---

## What you ship

- `projectmata/mobile-biometrics` installed and registered (course docs: `nativephp/mobile-biometrics` when published on Packagist)
- `RequireBiometricUnlock` middleware — redirects to `/locked` when session is not unlocked
- `UnlockGate` Livewire component — calls `Biometrics::authenticate()` on unlock
- Settings toggle to enable/disable lock (session + `FIELD_NOTES_LOCK_ENABLED`)
- Note routes wrapped in `biometric` middleware; Settings stays reachable while locked

Docs: [Biometrics](https://nativephp.com/docs/mobile/3/plugins/core/biometrics)

---

## Step 1 — Get the project

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/7-security-and-authentication/chapter-project-lock-field-notes
chmod +x setup.sh
./setup.sh
```

---

## Step 2 — Enable lock for device testing

In `.env`:

```dotenv
FIELD_NOTES_LOCK_ENABLED=true
```

Or enable via **Settings → Biometric lock** on device. Keep `false` for local browser/`php artisan serve` unless you want to exercise the locked screen.

---

## Step 3 — How the flow works

1. User opens Home → middleware checks `session('unlocked')`
2. If locked → redirect to `/locked` (`UnlockGate` Livewire component)
3. Component calls `Biometrics::authenticate()` (sync result with `projectmata/mobile-biometrics`)
4. On success → `session(['unlocked' => true])` → redirect to notes
5. On failure → locked UI with **Try again** (no note content exposed)

Biometrics are **async** — the middleware redirects rather than blocking on `prompt()` (see lesson 7.5).

---

## Step 4 — Verify locally

```bash
php artisan test --filter=BiometricLockTest
php artisan test
```

PHPUnit sets `FIELD_NOTES_LOCK_ENABLED=false` by default so CRUD tests stay unblocked.

---

## Step 5 — Verify on simulator

```bash
php artisan native:install --force
php artisan native:run ios
```

Checklist:

- [ ] Cold-start with lock enabled → biometric prompt before notes
- [ ] Cancel auth → locked screen, no note bodies visible
- [ ] Success → note list appears
- [ ] **Settings** reachable while locked; toggle disables lock
- [ ] Review `cleanup_env_keys` in `config/nativephp.php`

Enroll Face ID / Touch ID in the iOS Simulator (Features → Face ID) before testing.

---

## What changed from Chapter 6

| File | Change |
| ---- | ------ |
| `composer.json` | `livewire/livewire`, `projectmata/mobile-biometrics` |
| `config/field-notes.php` | **New** — `lock_enabled` env flag |
| `app/Http/Middleware/RequireBiometricUnlock.php` | **New** |
| `app/Livewire/UnlockGate.php` | **New** — async biometric handling |
| `app/Http/Controllers/SettingsController.php` | **New** — lock toggle |
| `resources/views/livewire/unlock-gate.blade.php` | **New** |
| `resources/views/layouts/locked.blade.php` | **New** — minimal unlock shell |
| `resources/views/settings.blade.php` | Biometric lock toggle |
| `routes/web.php` | `/locked`, `biometric` middleware group |
| `bootstrap/app.php` | `biometric` middleware alias |
| `tests/Feature/BiometricLockTest.php` | **New** |

Unchanged: SQLite CRUD, Share on edit, EDGE layout, branding assets.

---

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| Lock never appears | Set `FIELD_NOTES_LOCK_ENABLED=true` or enable in Settings; use `native:run` |
| Stuck on locked screen in browser | Expected — biometrics need device; disable lock for browser dev |
| `Class Biometrics not found` | `composer require projectmata/mobile-biometrics` + `native:plugin:register` |
| Notes visible without unlock | Confirm note routes are inside `Route::middleware('biometric')` |

---

## Remember

Chapter 8 adds push alerts and deep links — see [Chapter 8 — Deep link + sync stub](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/).

← [Field Notes capstone index](../../CAPSTONE.md)
