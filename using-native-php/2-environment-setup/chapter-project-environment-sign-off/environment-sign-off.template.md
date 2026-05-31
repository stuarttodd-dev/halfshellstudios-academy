# Environment sign-off — [Your name / team]

Fill this in after completing the Chapter 2 checklist. Replace every `[placeholder]`.

## Project overview

- **App name:** [Field Notes / your capstone name]
- **Laravel project path:** [e.g. `~/projects/chapter-project-first-mobile-screen`]
- **Course chapter:** 2 — Environment setup
- **Goal:** Document a reproducible dev environment and confirm the first **simulator** run with `native:run`.

## Machine profile

| Item | Your value |
| ---- | ---------- |
| Developer | [name] |
| Date signed off | [YYYY-MM-DD] |
| OS | [macOS 15 / Windows 11 / Ubuntu 24.04] |
| CPU arch | [arm64 / x86_64] |

## Toolchain (NativePHP Mobile v3)

| Tool | Required | Your version | Pass? |
| ---- | -------- | ------------ | ----- |
| PHP | 8.3+ | | ☐ |
| Composer | 2.x | | ☐ |
| Node.js | 20+ | | ☐ |
| Laravel (project) | 13.x | | ☐ |
| nativephp/mobile | 3.x | | ☐ |

## Platform tooling

### iOS simulator (macOS only)

| Item | Your value | Pass? |
| ---- | ---------- | ----- |
| Xcode installed | [version from App Store] | ☐ |
| Command line tools | `xcode-select -p` output | ☐ |
| Simulator opens | [device name, e.g. iPhone 16] | ☐ |

### Android emulator (optional this chapter)

| Item | Your value | Pass? |
| ---- | ---------- | ----- |
| Android Studio | [version or N/A] | ☐ |
| `adb devices` | [output or N/A] | ☐ |

## NativePHP configuration

```dotenv
NATIVEPHP_APP_ID=[com.yourcompany.yourapp]
```

| Check | Pass? |
| ----- | ----- |
| `NATIVEPHP_APP_ID` set in `.env` before `native:install` | ☐ |
| `php artisan native:install` completed | ☐ |
| `nativephp/` listed in `.gitignore` | ☐ |
| `php artisan list native` shows `native:run`, `native:jump` | ☐ |

## Verification commands run

Record output or “pass” for your machine:

```bash
php -v
composer -V
node -v
php artisan list native
grep NATIVEPHP_APP_ID .env
php artisan serve   # http://127.0.0.1:8000 — custom splash, not default welcome
php artisan native:jump   # optional: phone preview (Chapter 1)
php artisan native:run ios   # first simulator run (Chapter 2 sign-off)
```

## First simulator run sign-off

| Criterion | Pass? |
| --------- | ----- |
| iOS Simulator launched (or Android emulator if you chose `android`) | ☐ |
| App shows **It works on mobile** (not default Laravel welcome) | ☐ |
| No fatal error in terminal during build | ☐ |
| Screenshot saved for portfolio (optional) | ☐ |

## Known issues / workarounds

[List anything specific to your machine — VPN, firewall, wrong LAN IP, Defender exclusions on Windows, etc.]

## Sign-off

I confirm this environment matches the versions above and I can repeat the install on a clean checkout using the academy README steps.

**Signed:** ___________________ **Date:** ___________________
