# Environment sign-off — Field Notes (reference)

Reference sign-off for lesson **2.10**. Copy [`environment-sign-off.template.md`](environment-sign-off.template.md) for your own submission.

## Project overview

- **App name:** First Mobile Screen (Chapter 1 capstone)
- **Laravel project:** [`chapter-project-first-mobile-screen`](../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/)
- **Goal:** Reproducible NativePHP v3 toolchain + first **iOS Simulator** run via `native:run`

## Machine profile (example — macOS)

| Item | Reference value |
| ---- | --------------- |
| OS | macOS 15.x (Apple Silicon) |
| PHP | 8.3.x (`php -v`) |
| Composer | 2.8.x (`composer -V`) |
| Node.js | 22.x (`node -v`) |
| Laravel | 13.x (from project `composer.lock`) |
| nativephp/mobile | 3.3.x |

## Platform tooling

### iOS simulator (required for this sign-off)

| Item | Reference |
| ---- | --------- |
| Xcode | Latest from Mac App Store |
| CLI tools | `xcode-select --install` if `xcode-select -p` fails |
| Run command | `php artisan native:run ios` |

### Android (optional)

| Item | Reference |
| ---- | --------- |
| Android Studio | Installed with SDK 31+ |
| Run command | `php artisan native:run android` |

## NativePHP configuration

```dotenv
NATIVEPHP_APP_ID=com.halfshell.firstmobilescreen
```

- `php artisan native:install` run once with app ID set
- `nativephp/` in `.gitignore` (not committed)
- Start URL: `/` → `mobile-home` Blade view

## Verification record

```bash
./verify-environment.sh
cd ../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen
php artisan serve                    # browser: "It works on mobile"
php artisan native:run ios             # simulator sign-off
```

## First simulator run — success criteria

1. Simulator boots and shows the NativePHP shell.
2. Splash text **It works on mobile** on dark background.
3. Build completes without fatal errors in the terminal.

## Sign-off

Reference environment validated with [`verify-environment.sh`](verify-environment.sh) and [`run-ios-simulator.sh`](run-ios-simulator.sh).

**Date:** 2026-05-30
