# Chapter 2 — Environment sign-off and first simulator run

**Capstone:** [All chapters](../../CAPSTONE.md) · [← Chapter 1](../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/) · **Chapter 2** · [Chapter 3 →](../../3-webview-ui-branding-and-assets/chapter-project-icon-splash-note-list/)

**Course:** [Chapter project: environment sign-off and first simulator run](https://docker.learnio.dev/learn/sections/chapter-environment-setup/chapter-project-environment-sign-off-and-first-simulator-run)

**Previous:** [Chapter 1 — First mobile screen](../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/) · **Next:** [Chapter 3 — Icon, splash, note list](../../3-webview-ui-branding-and-assets/chapter-project-icon-splash-note-list/)

**Stack position:** Sign-off kit for your toolchain — scripts and checklist. The runnable app under test is [Chapter 1](../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/).

---

## What you will deliver

1. A completed **environment sign-off** document (your machine’s versions and pass/fail checks).
2. A **first simulator run** showing **It works on mobile** from the Chapter 1 splash page.

## Prerequisites

Complete [Chapter 1 — first screen on mobile](../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/) first:

- Laravel app with `nativephp/mobile` installed
- `php artisan native:install` done
- `NATIVEPHP_APP_ID` in `.env`
- Browser check at `http://127.0.0.1:8000` already passes

| Tool | Required |
| ---- | -------- |
| PHP 8.3+ | Yes |
| Composer 2.x | Yes |
| Node.js 20+ | Recommended (Vite / `native:watch`) |
| Xcode + iOS Simulator | Yes on macOS for `native:run ios` |
| Android Studio + SDK | Optional (`native:run android`) |

---

## Step 1 — Clone and open this folder

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/2-environment-setup/chapter-project-environment-sign-off
chmod +x verify-environment.sh run-ios-simulator.sh
```

---

## Step 2 — Install platform tooling

### macOS (iOS Simulator)

1. Install **Xcode** from the Mac App Store.
2. Install command line tools if needed:

   ```bash
   xcode-select --install
   xcode-select -p
   ```

3. Open Xcode once and install an **iPhone** simulator runtime (Settings → Platforms / Components).

### Android (optional)

1. Install [Android Studio](https://developer.android.com/studio).
2. Install SDK **API 31+** via SDK Manager.
3. Create an AVD or connect a device with USB debugging.
4. Verify:

   ```bash
   adb devices
   ```

### PHP, Composer, Node

Use [Laravel Herd](https://herd.laravel.com/), manual installs, or your team standard — NativePHP v3 expects **PHP 8.3+**, **Composer 2.x**, **Node 20+**.

---

## Step 3 — Prepare the Chapter 1 Laravel project

```bash
cd ../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen
./setup.sh
```

Confirm `.env` contains:

```dotenv
NATIVEPHP_APP_ID=com.yourname.firstmobilescreen
```

If you have not run NativePHP install yet:

```bash
php artisan native:install
```

Browser check:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) — you must see **It works on mobile**. Stop the server with `Ctrl+C`.

---

## Step 4 — Run automated environment checks

From the sign-off folder:

```bash
cd ../../2-environment-setup/chapter-project-environment-sign-off
./verify-environment.sh
```

Fix any **FAIL** lines before continuing. Set a custom project path if needed:

```bash
export NATIVEPHP_PROJECT=/path/to/your/laravel-app
./verify-environment.sh
```

---

## Step 5 — Fill in your sign-off document

```bash
cp environment-sign-off.template.md ~/my-environment-sign-off.md
```

Record your actual versions from:

```bash
php -v
composer -V
node -v
uname -a
xcode-select -p          # macOS
grep NATIVEPHP_APP_ID ../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/.env
```

Compare with the reference: [`environment-sign-off.md`](environment-sign-off.md).

---

## Step 6 — First simulator run

### iOS (macOS)

From this folder:

```bash
./run-ios-simulator.sh ios
```

Or from the Laravel project directly:

```bash
cd ../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen
php artisan native:run ios
```

First build can take several minutes. When the simulator opens, confirm:

- Dark splash page
- Heading **It works on mobile**
- Not the default Laravel welcome illustration

### Android

```bash
./run-ios-simulator.sh android
# or: php artisan native:run android
```

---

## Step 7 — Sign off

Tick every row in your sign-off doc:

- [ ] `verify-environment.sh` exits 0
- [ ] Browser splash verified (`php artisan serve`)
- [ ] Simulator/emulator shows the same splash via `native:run`
- [ ] `NATIVEPHP_APP_ID` and versions recorded
- [ ] Known issues / workarounds noted (VPN, firewall, etc.)

---

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`environment-sign-off.template.md`](environment-sign-off.template.md) | Blank sign-off for your submission |
| [`environment-sign-off.md`](environment-sign-off.md) | Reference example |
| [`verify-environment.sh`](verify-environment.sh) | Checks PHP, Composer, Node, Xcode/adb, NativePHP commands |
| [`run-ios-simulator.sh`](run-ios-simulator.sh) | Runs `native:run ios` (or `android`) on the Chapter 1 project |

## Jump vs native:run

| Command | Use |
| ------- | --- |
| `php artisan native:jump` | Fast preview on your **phone** over Wi‑Fi (Chapter 1) |
| `php artisan native:run ios` | Packaged build in **Simulator** — production-shaped (this project) |

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| `native:run` missing | `composer install` + `php artisan native:install` in Laravel project |
| Xcode / simctl errors | Install Xcode; run `xcode-select --install`; open Simulator app once |
| Wrong splash in simulator | Confirm Chapter 1 `routes/web.php` uses `view('mobile-home')` |
| Android build fails | SDK 31+ installed; `adb devices` shows emulator |
| Build very slow (Windows) | Add Defender exclusions (lesson 2.8) |

---

## Remember

**Document first, simulate second.** Your sign-off proves the toolchain is pinned and repeatable; `native:run` proves the same Laravel code runs in a native shell — not just in a browser or Jump.

← [Field Notes capstone index](../../CAPSTONE.md)
