# Chapter 9 — Field Notes: release candidate

**Capstone:** [All chapters](../../CAPSTONE.md) · [← Chapter 8](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/) · **Chapter 9**

**Course:** [Chapter project: Field Notes release candidate](https://php-apps.learnio.dev/learn/sections/chapter-deployment-and-store-submission/chapter-project-field-notes-release-candidate)

**Academy solution (GitHub):** [chapter-project-release-candidate](https://github.com/stuarttodd-dev/halfshellstudios-academy/tree/main/using-native-php/9-deployment-and-store-submission/chapter-project-release-candidate)

**Previous:** [Chapter 8 — Deep link + sync stub](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/) · **Next:** — (capstone complete)

**Built on:** [Chapter 8 — Deep link + sync stub](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/) · **You add:** versioned release builds, store checklist, release verification scripts. No new app features.

| Chapter | Solution |
| ------- | -------- |
| [1](../../1-introduction-and-mobile-architecture/chapter-project-first-mobile-screen/) | First mobile screen |
| [2](../../2-environment-setup/chapter-project-environment-sign-off/) | Environment sign-off |
| [3](../../3-webview-ui-branding-and-assets/chapter-project-icon-splash-note-list/) | Icon, splash, note list |
| [4](../../4-edge-native-navigation/chapter-project-edge-shell/) | EDGE shell |
| [5](../../5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/) | Send as text |
| [6](../../6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/) | Offline CRUD |
| [7](../../7-security-and-authentication/chapter-project-lock-field-notes/) | Biometric lock |
| [8](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/) | Deep link + sync stub |
| **9** | **Release candidate** |

---

## What you ship

- Release versioning in `.env` (`NATIVEPHP_APP_VERSION`, `NATIVEPHP_APP_VERSION_CODE`)
- Settings screen shows version + privacy policy URL
- `scripts/verify-release-candidate.sh` — pre-flight checks before `native:release`
- Store submission checklist and post-deploy smoke test (below)
- Same app feature set as Chapter 8

Docs: [Packaging & release](https://nativephp.com/docs/mobile/3/deployment/packaging-and-release)

---

## Step 1 — Get the project

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/9-deployment-and-store-submission/chapter-project-release-candidate
chmod +x setup.sh scripts/verify-release-candidate.sh
./setup.sh
```

---

## Step 2 — Version bump

In `.env`:

```dotenv
NATIVEPHP_APP_VERSION=1.0.0
NATIVEPHP_APP_VERSION_CODE=1
NATIVEPHP_APP_ID=com.halfshell.fieldnotes
NATIVEPHP_DEVELOPMENT_TEAM=YOUR_APPLE_TEAM_ID
FIELD_NOTES_PRIVACY_POLICY_URL=https://your-domain.com/privacy
APP_DEBUG=false
```

Increment `NATIVEPHP_APP_VERSION_CODE` (Android) and version name for every store upload.

---

## Step 3 — Pre-flight checks

```bash
./scripts/verify-release-candidate.sh
php artisan test
```

Confirms icon/splash assets, env keys, `cleanup_env_keys`, and PHPUnit pass.

---

## Step 4 — Release builds

**iOS** (Apple Developer Program + team ID in `.env`):

```bash
php artisan native:release ios
```

Upload to **App Store Connect** → **TestFlight** for internal testers.

**Android** (Play Console app + signing configured):

```bash
php artisan native:release android
```

Upload the AAB to **Internal testing** in Google Play Console.

---

## Step 5 — Store checklist

Before submitting (even internal tracks):

- [ ] Icon and splash match [Chapter 3](../../3-webview-ui-branding-and-assets/chapter-project-icon-splash-note-list/) assets
- [ ] Privacy policy URL set and reachable (`FIELD_NOTES_PRIVACY_POLICY_URL`)
- [ ] Permission strings accurate (biometrics, push — audit plugin manifests)
- [ ] `cleanup_env_keys` strips secrets (`FIREBASE_CREDENTIALS`, etc.)
- [ ] Screenshots from a real device build
- [ ] Same `NATIVEPHP_APP_ID` as development
- [ ] Firebase config files in project root if using push ([Chapter 8](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/))

---

## Step 6 — Post-deploy smoke test

On each store build (TestFlight / Play internal):

1. Create, edit, and delete a note (SQLite CRUD)
2. **Send as text** from note edit ([Chapter 5](../../5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/))
3. Push notification opens note via deep link ([Chapter 8](../../8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/))
4. Biometric lock if enabled ([Chapter 7](../../7-security-and-authentication/chapter-project-lock-field-notes/))
5. EDGE tabs: Home, Compose, Settings ([Chapter 4](../../4-edge-native-navigation/chapter-project-edge-shell/))

---

## What changed from Chapter 8

| File | Change |
| ---- | ------ |
| `.env.example` | Release version, team ID, privacy policy URL |
| `config/field-notes.php` | `privacy_policy_url` |
| `resources/views/settings.blade.php` | Version + privacy link |
| `scripts/verify-release-candidate.sh` | **New** — release pre-flight |
| `tests/Feature/ReleaseCandidateTest.php` | **New** |

Unchanged: all Chapter 8 features (CRUD, lock, deep links, push, sync stub).

---

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| iOS release fails signing | Set `NATIVEPHP_DEVELOPMENT_TEAM`; valid Apple Developer membership |
| Android AAB rejected | Package name must match Firebase + `NATIVEPHP_APP_ID` |
| TestFlight build wrong version | Bump `NATIVEPHP_APP_VERSION*` and rebuild |
| Reviewer cannot test | Provide demo notes in App Store / Play review fields |

---

## Remember

[Chapter 10 — Capstone demo](https://php-apps.learnio.dev/learn/sections/chapter-course-closeout/capstone-demo-field-notes-walkthrough) walks through this release candidate in a demo — no new features.

← [Field Notes capstone index](../../CAPSTONE.md)
