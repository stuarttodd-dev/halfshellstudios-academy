# Chapter 5 — Field Notes: send a note as text (Share plugin)

**Capstone:** [All chapters](../../CAPSTONE.md) · [← Chapter 4](../../4-edge-native-navigation/chapter-project-edge-shell/) · **Chapter 5** · [Chapter 6 →](../../6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/)

**Course:** [Chapter project: photo attachment in Field Notes](https://php-apps.learnio.dev/learn/sections/chapter-native-functions-plugins-and-core-apis/chapter-project-photo-attachment-in-field-notes) *(lesson title; milestone is **send as text** via Share)*

**Previous:** [Chapter 4 — EDGE shell](../../4-edge-native-navigation/chapter-project-edge-shell/) · **Next:** [Chapter 6 — Offline CRUD](../../6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/)

**Built on:** [Chapter 4 — EDGE shell](../../4-edge-native-navigation/chapter-project-edge-shell/) · **You add:** `nativephp/mobile-share`, Compose form, `Share::file()`.

---

## What you ship

- `nativephp/mobile-share` installed and registered
- `ComposeController` — show form, validate body, call `Share::file('Field Notes', $body, '')`
- Compose tab with textarea + **Send as text** button
- PHPUnit: `SendNoteAsTextTest` mocks the Share facade

---

## Step 1 — Get the project

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text
chmod +x setup.sh
./setup.sh
```

`setup.sh` runs `composer install`, registers `nativephp/mobile-share`, and builds frontend assets.

If you continue from your own Chapter 4 repo:

```bash
composer require nativephp/mobile-share
php artisan native:plugin:register nativephp/mobile-share
```

Docs: [Share plugin](https://nativephp.com/docs/mobile/3/plugins/core/share)

---

## Step 2 — Compose form and controller

- **GET** `/compose` → `ComposeController@show`
- **POST** `/compose/send` → validates `body`, opens share sheet, redirects with flash

The empty third argument to `Share::file()` means **text only** (no attachment path).

---

## Step 3 — Verify in browser

```bash
php artisan serve
```

Open Compose, submit text — in the browser the Share facade may no-op; you still get the redirect and status flash.

```bash
php artisan test --filter=SendNoteAsTextTest
```

---

## Step 4 — Verify on simulator (required for Share UI)

```bash
php artisan native:install --force
php artisan native:run ios
```

Checklist:

- [ ] Open **Compose**, type a short message, tap **Send as text**
- [ ] System **share sheet** opens
- [ ] Choose **Messages** — body appears in the draft
- [ ] Home and Settings unchanged from Chapter 4

Optional — Jump (same Wi‑Fi): `php artisan native:jump` (packaged `native:run` is more reliable for plugins).

---

## What changed from Chapter 4

| File | Change |
| ---- | ------ |
| `composer.json` | `nativephp/mobile-share` dependency |
| `app/Http/Controllers/ComposeController.php` | **New** — form + `Share::file()` |
| `resources/views/compose.blade.php` | Form, validation errors, status flash |
| `routes/web.php` | Compose GET + `compose.send` POST |
| `setup.sh` | Registers Share plugin |
| `tests/Feature/SendNoteAsTextTest.php` | **New** |

Unchanged: EDGE layout, icon/splash, static home rows, no SQLite.

---

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| `Class Share not found` | `composer require nativephp/mobile-share` and `php artisan native:plugin:register nativephp/mobile-share` |
| Nothing happens on Send | Test with `native:run` on simulator/device, not browser alone |
| Plugin missing from build | Re-run `native:run` after registering the plugin |

---

## Remember

Chapter 6 saves notes in SQLite — see [Chapter 6 — Offline CRUD](../../6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/).

← [Field Notes capstone index](../../CAPSTONE.md)
