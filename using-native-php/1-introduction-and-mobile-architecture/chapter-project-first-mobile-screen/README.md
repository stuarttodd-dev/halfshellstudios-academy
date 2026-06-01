# Chapter 1 — Field Notes: first mobile screen

**Capstone:** [All chapters](../../CAPSTONE.md) · **Chapter 1** · [Chapter 2 →](../../2-environment-setup/chapter-project-environment-sign-off/)

**Course:** [Chapter project: first screen on mobile (Jump)](https://php-apps.learnio.dev/learn/sections/chapter-introduction-and-mobile-architecture/chapter-project-first-mobile-screen-on-jump)

**Previous:** — · **Next:** [Chapter 2 — Environment sign-off](../../2-environment-setup/chapter-project-environment-sign-off/)

**Stack position:** Starting point of the Field Notes capstone — minimal Laravel app on **Jump**.

---

## What you will see when it works

- **Browser:** dark page, heading **It works on mobile**
- **Phone (Jump):** the same page — not the default Laravel welcome art

## Prerequisites

Install before you start:

| Requirement | Notes |
| ----------- | ----- |
| PHP 8.3+ | `php -v` |
| Composer | `composer -V` |
| Node.js 20+ | For optional Vite HMR with `npm run dev` |
| Jump on your phone | [bifrost.nativephp.com/jump](https://bifrost.nativephp.com/jump) |
| Same Wi‑Fi | Phone and laptop on one network; turn off VPN if Jump cannot connect |

---

## Step 1 — Get the project

From anywhere:

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen
```

Or, if you already have the academy repo:

```bash
cd using-native-php/1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen
```

---

## Step 2 — Install Laravel dependencies

```bash
chmod +x setup.sh
./setup.sh
```

`setup.sh` runs `composer install` and `php artisan key:generate`. It creates `.env` from `.env.example` if you do not have one yet.

---

## Step 3 — Set your app identifier

Open `.env` and set a unique bundle id (reverse-DNS style):

```dotenv
NATIVEPHP_APP_ID=com.yourname.firstmobilescreen
```

Keep the example value if you are only testing locally:

```dotenv
NATIVEPHP_APP_ID=com.halfshell.firstmobilescreen
```

---

## Step 4 — Install NativePHP Mobile

```bash
php artisan native:install
```

This adds NativePHP config and generates the `nativephp/` directory on your machine. That folder is gitignored — it is recreated per developer, not shipped in the repo.

If install fails, confirm `NATIVEPHP_APP_ID` is set in `.env` **before** running the command.

---

## Step 5 — Verify in the browser (browser first)

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

You must see **It works on mobile** on a dark background. If you still see the default Laravel welcome page, check that `routes/web.php` returns `view('mobile-home')` and that `resources/views/mobile-home.blade.php` exists.

Stop the dev server with `Ctrl+C` before Jump — unless you use `--no-serve` in step 7.

Optional quick test:

```bash
php artisan test --filter=MobileHomeTest
```

---

## Step 6 — Install Jump on your phone

1. Install **Jump** from [bifrost.nativephp.com/jump](https://bifrost.nativephp.com/jump).
2. Connect phone and laptop to the **same Wi‑Fi**.
3. Disable VPN on either device if pairing fails.

---

## Step 7 — Run Jump and scan the QR code

From the project root:

```bash
php artisan native:jump
```

A QR code appears in the terminal. Open Jump on your phone, scan it, and wait for the app to load.

**If you kept `php artisan serve` running** in another terminal:

```bash
php artisan native:jump --no-serve --laravel-port=8000
```

**If Jump picks the wrong network interface** (Wi‑Fi + Ethernet, or VPN):

```bash
php artisan native:jump --ip=192.168.1.42
```

Replace `192.168.1.42` with your laptop’s LAN IP (`ipconfig getifaddr en0` on macOS, or your system network settings).

Optional — live CSS/JS reload while Jump is connected (second terminal):

```bash
npm install
npm run dev
```

---

## Step 8 — Confirm success

You are done when all of these are true:

1. Jump connects without a network error.
2. Your phone shows the dark page with **It works on mobile**.
3. Edit the `<h1>` in `resources/views/mobile-home.blade.php`, save, refresh Jump (or use HMR with `npm run dev`) — the phone updates.

That is Laravel running on your mobile device through NativePHP Jump.

---

## How it fits together

```text
Your Mac                          Your phone
┌─────────────────────┐          ┌──────────────────┐
│ Laravel (PHP)       │  Wi-Fi   │ Jump app         │
│ routes/web.php      │ ◄──────► │ loads your app   │
│ mobile-home.blade   │  QR pair │ in a native shell│
└─────────────────────┘          └──────────────────┘
```

Later in the course you will use `php artisan native:run` to build a standalone iOS/Android app. For this chapter project, **Jump is enough** — same Laravel code, preview on device in minutes.

---

## Key files in this solution

| File | Purpose |
| ---- | ------- |
| [`routes/web.php`](routes/web.php) | `/` → `mobile-home` view |
| [`resources/views/mobile-home.blade.php`](resources/views/mobile-home.blade.php) | Custom splash screen |
| [`.env.example`](.env.example) | `NATIVEPHP_APP_ID` template |
| [`composer.json`](composer.json) | Includes `nativephp/mobile` |

---

## If it goes wrong

| Symptom | What to try |
| ------- | ----------- |
| Jump cannot connect | Same Wi‑Fi; turn off VPN; `native:jump --ip=YOUR_LAN_IP` |
| Blank screen after scan | `php artisan key:generate`; read `storage/logs/laravel.log` |
| Still see Laravel welcome page | Confirm route uses `view('mobile-home')` and Blade file exists |
| Port already in use | Stop other `artisan serve` processes or use `--laravel-port=` |
| `native:install` fails | Set `NATIVEPHP_APP_ID` in `.env` first |
| `composer install` fails | PHP 8.3+ required |

For Xcode, Android Studio, and simulator builds, see chapter 2–3 in the course.

---

## Remember

**Browser first, phone second.** Get the page right at `http://127.0.0.1:8000`, then prove the same Laravel code on device through Jump.

← [Field Notes capstone index](../../CAPSTONE.md)
