# Chapter 4 — Field Notes: EDGE shell (top bar + bottom nav)

**Course page:** [Chapter project: EDGE shell for Field Notes](https://docker.learnio.dev/learn/sections/chapter-edge-native-navigation/chapter-project-edge-shell-for-field-notes)

**Stack position:** This is the **Chapter 4 state** of the Field Notes capstone — everything from [Chapter 3](../3-webview-ui-branding-and-assets/chapter-project-icon-splash-note-list/), plus native EDGE navigation: a persistent top bar and bottom tab bar with Home, Compose, and Settings routes.

| Chapter | What you added |
| ------- | -------------- |
| 1 | `mobile-home` on Jump |
| 2 | `native:run` on simulator |
| 3 | Icon, splash, branded home, placeholder note rows |
| **4** | **EDGE layout, three routes, native top + bottom nav** |
| 5 | Share sheet from Compose (coming) |
| 6 | Real offline CRUD (coming) |

---

## What you ship

- `resources/views/layouts/app.blade.php` — shared shell with `native:top-bar` and `native:bottom-nav`
- Three named routes: `home`, `compose`, `settings`
- Home keeps the Chapter 3 note-list placeholders; Compose and Settings are placeholders for later chapters
- PHPUnit coverage in `EdgeShellTest`

---

## Step 1 — Get the project

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/4-edge-native-navigation/chapter-project-edge-shell
chmod +x setup.sh
./setup.sh
```

If you are continuing from your own Chapter 3 repo, apply the diff in [What changed from Chapter 3](#what-changed-from-chapter-3).

---

## Step 2 — EDGE layout

The layout wraps every screen:

- **`native:top-bar`** — title and subtitle per page (`@section('top_title')`, `@section('top_subtitle')`)
- **`native:bottom-nav`** — Home / Compose / Settings with `:active` driven by `request()->routeIs()`
- **`main.pb-20`** — content clears the tab bar

Docs: [Top Bar](https://nativephp.com/docs/mobile/3/edge-components/top-bar) · [Bottom Navigation](https://nativephp.com/docs/mobile/3/edge-components/bottom-navigation)

---

## Step 3 — Routes and views

```php
Route::get('/', fn () => view('home'))->name('home');
Route::get('/compose', fn () => view('compose'))->name('compose');
Route::get('/settings', fn () => view('settings'))->name('settings');
```

| View | Purpose |
| ---- | ------- |
| `home.blade.php` | Note list placeholders from Chapter 3 |
| `compose.blade.php` | Placeholder — Chapter 5 adds Share |
| `settings.blade.php` | Placeholder — Chapter 7 adds biometric lock |

The standalone `mobile-home.blade.php` from Chapter 3 is replaced by `home.blade.php` extending the layout.

Verify in the browser:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) and click through `/`, `/compose`, `/settings`.

```bash
php artisan test --filter=EdgeShellTest
```

---

## Step 4 — Confirm on simulator

```bash
php artisan native:run ios
# or: php artisan native:run android
```

Checklist:

- [ ] Native **bottom tab bar** visible on every screen (not just HTML links)
- [ ] Active tab highlights correctly when you switch routes
- [ ] **Top bar** title updates per screen
- [ ] Home still shows branded note list with safe areas
- [ ] Icon and splash unchanged from Chapter 3

Optional — Jump preview (same Wi‑Fi):

```bash
php artisan native:jump
```

The native tab bar should persist across navigations without flashing away.

---

## What changed from Chapter 3

| File | Change |
| ---- | ------ |
| `resources/views/layouts/app.blade.php` | **New** — EDGE top bar + bottom nav shell |
| `resources/views/home.blade.php` | **New** — Chapter 3 note list content, extends layout |
| `resources/views/compose.blade.php` | **New** — Compose placeholder |
| `resources/views/settings.blade.php` | **New** — Settings placeholder |
| `resources/views/mobile-home.blade.php` | **Removed** — content moved to `home.blade.php` |
| `routes/web.php` | Three named routes instead of single `/` view |
| `tests/Feature/EdgeShellTest.php` | **New** — route + EDGE markup tests |

Unchanged from Chapter 3: icon/splash assets, Tailwind branding, static note rows, no database.

---

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| Bottom nav renders as plain HTML | Run on device/simulator or Jump — EDGE components need the native WebView |
| Content hidden under tab bar | Confirm `<main class="pb-20">` in the layout |
| Wrong tab highlighted | Check `:active` uses `request()->routeIs('home')` etc. and routes are named |
| 404 on `/compose` or `/settings` | Confirm `routes/web.php` and run `php artisan route:list` |

---

## Remember

Chapter 5 wires Compose to the Share sheet — see [chapter-project-send-note-as-text](../../5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/). Chapter 6 replaces placeholder rows with SQLite-backed notes.

← [Using Native PHP](../../README.md)
