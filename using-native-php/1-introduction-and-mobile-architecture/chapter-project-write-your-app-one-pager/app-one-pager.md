# Field Notes

Offline-first note capture for people who work away from reliable Wi‑Fi — technicians, inspectors, and anyone who needs to record what they saw and sync when they can.

This is the **course capstone** reference one-pager for [Using Native PHP](https://docker.learnio.dev/learn/sections/chapter-introduction-and-mobile-architecture/chapter-project-write-your-app-one-pager-features-plugins-platforms). Copy the structure for your own app idea; keep service names and chapter checkpoints aligned with what you can demo on a real device.

## Audience

- **Primary user:** Field worker who captures short notes, photos, and status updates during site visits.
- **Context:** Mostly offline on phone; syncs to a Laravel API when connectivity returns. Same Laravel codebase ships to **iOS and Android** via NativePHP Mobile v3.

## Features

Mapped to what you will ship across chapters 1–16:

| # | Feature | User outcome | Course checkpoint |
| - | ------- | ------------ | ----------------- |
| 1 | **Sign-in & secure session** | User logs in once; session/token survives app restarts | Ch 9 — secure token storage |
| 2 | **Offline notes CRUD** | Create, read, update, delete notes with no network | Ch 10 — `OfflineNote` + SQLite |
| 3 | **Sync queue** | Pending writes push when online; failures stay retryable | Ch 14 — background sync |
| 4 | **Profile photo** | Avatar from device camera, stored locally first | Ch 11 — camera / media |
| 5 | **Note attachments** | Attach one photo per note from camera roll | Ch 11 — media feature |
| 6 | **Share / export** | Share note text or export bundle to another app | Ch 12 — share export drill |
| 7 | **Deep link to note** | `fieldnotes://notes/{id}` opens detail screen | Ch 13 — deep links |
| 8 | **Push stub** | Register device token; show local notification on sync complete | Ch 13 — push stub |
| 9 | **EDGE shell + bottom nav** | Native-feeling chrome: **Notes · Activity · Settings** | Ch 8 — EDGE + 3 sections |
| 10 | **Biometric unlock** | Optional Face ID / fingerprint before showing notes | Ch 7 — native-backed feature #1 |
| 11 | **Store-ready identity** | App name, icon, version, privacy policy placeholders | Ch 1.14 + Ch 16 release candidate |

### Architecture (one line)

Laravel MVC on device: Blade/Livewire UI → controllers → Eloquent/SQLite → optional API sync. Native capabilities via registered plugins only.

```text
UI (EDGE + web views) → Laravel routes/controllers → SQLite (local) ⇄ API (when online)
                              ↓
                    NativePHP bridges (camera, biometrics, push, share)
```

## Plugins

| Plugin (Composer package) | Type | What it unlocks | Register? |
| ------------------------- | ---- | --------------- | --------- |
| `nativephp/mobile` | official (core) | Embedded PHP runtime, Jump, `native:run`, bridges | bundled |
| `nativephp/mobile-biometrics` | official | Face ID / fingerprint gate on app open | `native:plugin:register` |
| Camera plugin from [Plugin Marketplace](https://nativephp.com/plugins/marketplace) | official | Capture profile + note photos | yes |
| Secure storage / keychain wrapper (marketplace or custom) | official or custom | Persist auth token outside plain SQLite | yes if not core |
| Push notifications plugin (course stub) | official or community | Device token + local/remote notification demo | yes |

**Plugin decision rule (lesson 1.8):** marketplace official first → reviewed community second → custom package only for a gap no plugin covers.

**Verify after install:**

```bash
composer require nativephp/mobile-biometrics
php artisan native:plugin:register nativephp/mobile-biometrics
php artisan native:plugin:list
php artisan native:run
```

Replace camera/push package names with the exact names from the plugin page before pinning them in this document.

## Platforms

| Platform | Ship in v1? | Minimum | Notes |
| -------- | ----------- | ------- | ----- |
| **iOS** | yes | iOS 16+ | Simulator for dev loop; real device for camera, biometrics, push |
| **Android** | yes | API 31 (Android 12+) | USB debugging + one physical device for parity checks |
| **Web (browser)** | no (dev only) | — | Laravel runs in browser first (chapter 2 rule); not a store target for this capstone |

**Primary dev path:** Jump on same Wi‑Fi → `php artisan native:run` on simulator → one signed build on each platform before chapter 16.

## Non-goals (v1)

- Multi-tenant orgs or team admin console
- Real-time collaborative editing
- Production push campaign infrastructure (stub only)
- Custom native modules beyond one evaluated plugin gap (chapter 15)
- iPad-optimised layout (phone-first; tablet may work but not a acceptance criterion)

## Data & offline model

- **Local source of truth:** SQLite via Laravel migrations on device
- **`OfflineNote` fields (minimum):** `body`, `sync_state` (`pending` \| `synced` \| `failed`), timestamps
- **Write path:** validate → save locally with `sync_state = pending` → queue sync job when online
- **Read path:** render from SQLite immediately; merge remote updates when sync succeeds

## Success criteria

Before moving to chapter 2 environment setup:

- [ ] One sentence problem statement a non-developer understands
- [ ] Every feature row above maps to a screen you can name (`/notes`, `/activity`, `/settings`)
- [ ] Plugin list uses real Composer package names from the marketplace (not placeholders)
- [ ] iOS **and** Android both marked yes with a device you can actually plug in
- [ ] Non-goals prevent scope creep into admin dashboards or full push infrastructure
- [ ] You can explain why Laravel stays the foundation (lesson 1.3) in two sentences

## If the app looks fine but behaviour is wrong (debug ladder)

When a screen renders but native behaviour fails, run this order **before** rewriting config or ripping out plugins:

```bash
php artisan native:plugin:list
php artisan native:run --verbose
# In another terminal while reproducing:
tail -f storage/logs/laravel.log
```

One sentence for a teammate: **check `native:plugin:list` and Laravel logs first** — a running WebView does not prove the bridge registered, and most “camera/biometrics does nothing” bugs are missing `native:plugin:register` or denied OS permissions, not bad Blade.

## Revision log

| Date | Change |
| ---- | ------ |
| 2026-05-29 | Initial capstone one-pager for course reference |
