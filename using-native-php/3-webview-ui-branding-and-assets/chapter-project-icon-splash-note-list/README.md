# Chapter 3 — Field Notes: icon, splash, and note list UI shell

**Course page:** [Chapter project: icon, splash, and note list UI](https://docker.learnio.dev/learn/sections/chapter-webview-ui-branding-and-assets/chapter-project-icon-splash-and-note-list-ui)

**Stack position:** This is the **Chapter 3 state** of the Field Notes capstone — same Laravel app as [Chapter 1](../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/) and [Chapter 2](../2-environment-setup/chapter-project-environment-sign-off/), now with app icon, splash screens, Tailwind branding, and a static note-list UI shell (no database yet).

| Chapter | What you added |
| ------- | -------------- |
| 1 | `mobile-home` on Jump |
| 2 | `native:run` on simulator |
| **3** | **Icon, splash, branded home, placeholder note rows** |
| 6 | Real offline CRUD (coming) |

---

## What you ship

- `public/icon.png` (1024×1024) on the simulator home screen
- `public/splash.png` (+ optional `splash-dark.png`) on cold start
- Branded **Field Notes** home with Tailwind, safe areas, static note list UI
- `config/nativephp.php` → `android.status_bar_style` = `light`

---

## Step 1 — Get the project

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/3-webview-ui-branding-and-assets/chapter-project-icon-splash-note-list
chmod +x setup.sh
./setup.sh
```

If you are continuing your own Chapter 1 repo instead of cloning this folder, apply the diff described in [What changed from Chapter 1](#what-changed-from-chapter-1).

---

## Step 2 — App icon

Icons live in `public/icon.png` (1024×1024, solid background, no transparency).

Regenerate defaults or replace with your design, then refresh native assets:

```bash
php generate-branding-assets.php   # optional: rebuild placeholder PNGs
php artisan native:install --force
```

Docs: [App Icon](https://nativephp.com/docs/mobile/3/the-basics/app-icon)

**Jump does not update the launcher icon** — use `native:run` on a simulator or device to verify.

---

## Step 3 — Splash screens

```bash
public/icon.png
public/splash.png          # portrait, min 1080×1920
public/splash-dark.png     # optional dark mode
```

After adding or changing files:

```bash
php artisan native:install --force
```

Docs: [Splash Screens](https://nativephp.com/docs/mobile/3/the-basics/splash-screens)

---

## Step 4 — Branded home + note list UI shell

The home route still serves `mobile-home` — now with:

- `viewport-fit=cover` and `user-scalable=no`
- `class="nativephp-safe-area"` on `<body>`
- Tailwind via Vite (`@vite` in the Blade file)
- **Field Notes** header and three **static** placeholder note rows (no DB — chapter 6 adds CRUD)

Verify in the browser:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

```bash
php artisan test --filter=MobileHomeTest
```

---

## Step 5 — Android status bar

Already set in [`config/nativephp.php`](config/nativephp.php):

```php
'android' => [
    'status_bar_style' => 'light',
],
```

Use `light` on dark backgrounds so clock/icons stay readable.

---

## Step 6 — Confirm on simulator

```bash
php artisan native:run ios
# or: php artisan native:run android
```

Checklist:

- [ ] Custom **Field Notes** icon on the home screen (not default Laravel / NativePHP)
- [ ] Splash shows on cold start
- [ ] App opens to branded note list UI with safe areas
- [ ] Same Blade works in browser and simulator

Optional — Jump preview (same Wi‑Fi):

```bash
php artisan native:jump
```

---

## What changed from Chapter 1

| File | Change |
| ---- | ------ |
| `public/icon.png`, `splash*.png` | Native branding assets |
| `generate-branding-assets.php` | Regenerates placeholder PNGs (PHP GD) |
| `resources/views/mobile-home.blade.php` | Tailwind, Field Notes, note list shell |
| `config/nativephp.php` | `status_bar_style` → `light` |
| `.env.example` | `APP_NAME`, `NATIVEPHP_APP_ID=com.halfshell.fieldnotes` |
| `setup.sh` | Builds Vite assets + generates PNGs |

Unchanged from Chapter 1: single route `/`, no database, no plugins beyond `nativephp/mobile`.

---

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| Default launcher icon | Confirm `public/icon.png` exists; `php artisan native:install --force`; use `native:run` not Jump alone |
| Splash unchanged | Re-run install with `--force` |
| Content under notch | `nativephp-safe-area` + `viewport-fit=cover` on `<body>` |
| Unstyled HTML | Run `npm run build` or `npm run dev` |
| GD missing for asset script | Install PHP GD or drop in your own PNGs manually |

---

## Remember

Chapter 4 adds EDGE navigation around this shell. Chapter 6 replaces placeholder rows with real SQLite-backed notes.

← [Using Native PHP](../../README.md)
