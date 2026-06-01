# Chapter 5 — Field Notes: send a note as text (Share plugin)

**Course page:** [Chapter project: photo attachment in Field Notes](https://docker.learnio.dev/learn/sections/chapter-native-functions-plugins-and-core-apis/chapter-project-photo-attachment-in-field-notes) *(lesson title; this milestone is **send as text** via Share)*

**Stack position:** This is the **Chapter 5 state** of the Field Notes capstone — everything from [Chapter 4](../../4-edge-native-navigation/chapter-project-edge-shell/), plus the **Share** plugin on Compose so users can open the system share sheet with note body text (Messages, Mail, etc.). No database yet.

| Chapter | What you added |
| ------- | -------------- |
| 1 | `mobile-home` on Jump |
| 2 | `native:run` on simulator |
| 3 | Icon, splash, branded home, placeholder note rows |
| 4 | EDGE top bar + bottom nav (Home / Compose / Settings) |
| **5** | **Share plugin — Compose form + `Share::file()` send as text** |
| 6 | SQLite offline CRUD (coming) |

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

Chapter 6 saves notes in SQLite and lists them on Home. Optional camera photo attach (lessons 5.4–5.5) can layer on Compose before or after CRUD.

← [Using Native PHP](../../README.md)
