# Chapter 8 — Field Notes: deep link + push + sync stub

**Capstone:** [All chapters](../../CAPSTONE.md) · [← Chapter 7](../../7-security-and-authentication/chapter-project-lock-field-notes/) · **Chapter 8** · [Chapter 9 →](../../9-deployment-and-store-submission/chapter-project-release-candidate/)

**Course:** [Chapter project: deep link and sync stub](https://docker.learnio.dev/learn/sections/chapter-deep-links-push-and-background-sync/chapter-project-deep-link-and-sync-stub)

**Previous:** [Chapter 7 — Biometric lock](../../7-security-and-authentication/chapter-project-lock-field-notes/) · **Next:** [Chapter 9 — Release candidate](../../9-deployment-and-store-submission/chapter-project-release-candidate/)

**Built on:** [Chapter 7 — Biometric lock](../../7-security-and-authentication/chapter-project-lock-field-notes/) · **You add:** deep links, push enrollment, `SyncNoteToApi` job stub.

---

## What you ship

- Deep link scheme `fieldnotes://app/notes/{id}` via `NATIVEPHP_DEEPLINK_*` env keys
- `GET /notes/{note}` → read-only `notes.show` (push tap target)
- `NoteDeepLink` helper + `php artisan field-notes:push-payload {id}` for dev payloads
- Push enrollment on Settings (`PushNotifications::enroll()` + `TokenGenerated` → SQLite)
- `SyncNoteToApi` job dispatched after save/update (`QUEUE_CONNECTION=database`)
- Firebase placeholder config: `google-services.json.example`, `GoogleService-Info.plist.example`

Docs: [Deep Links](https://nativephp.com/docs/mobile/3/concepts/deep-links) · [Push Notifications](https://nativephp.com/docs/mobile/3/concepts/push-notifications) · [Queues on device](https://nativephp.com/docs/mobile/3/concepts/queues)

---

## Step 1 — Get the project

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub
chmod +x setup.sh
./setup.sh
```

---

## Step 2 — Deep link scheme

In `.env`:

```dotenv
NATIVEPHP_DEEPLINK_SCHEME=fieldnotes
NATIVEPHP_DEEPLINK_HOST=app
```

Maps `fieldnotes://app/notes/1` → Laravel route `/notes/1` (`notes.show`).

Test in the browser (with lock disabled or session unlocked):

```bash
php artisan serve
# http://127.0.0.1:8000/notes/1
```

After changing scheme/host:

```bash
php artisan native:install --force
```

---

## Step 3 — Push notifications

1. Create a Firebase project; download real `google-services.json` and `GoogleService-Info.plist` to the **project root** (see `.example` files).
2. Match package/bundle ID to `NATIVEPHP_APP_ID=com.halfshell.fieldnotes`.
3. Rebuild: `php artisan native:run ios`
4. On **Settings**, tap **Enable push notifications** — token saves to `device_push_tokens`.

Print a sample FCM payload (title, body = lock-screen text, deep-link path):

```bash
php artisan field-notes:push-payload 1
```

When `nativephp/mobile-firebase` is available, follow lesson 8.4 for server-side `fcm:send`. Push enrollment uses the core `PushNotifications` facade from `nativephp/mobile`.

---

## Step 4 — Background sync stub

Saving a note writes to SQLite immediately, then dispatches `SyncNoteToApi`:

```dotenv
QUEUE_CONNECTION=database
```

The job logs the note id and clears `sync_pending` (stub — replace with HTTPS to your API). On device, NativePHP runs the queue worker automatically; no `queue:work` on the phone.

---

## Step 5 — Manual deep link test (Android)

```bash
adb shell am start -a android.intent.action.VIEW -d "fieldnotes://app/notes/1"
```

iOS Simulator: open the same URL in Safari if the scheme is registered.

---

## Step 6 — Confirm success

- [ ] `/notes/{id}` shows read-only note (same screen push opens)
- [ ] `field-notes:push-payload` prints body text + `/notes/{id}` path
- [ ] Push token stored after enroll on device
- [ ] New note shows **Sync pending**, clears after job runs
- [ ] `FIREBASE_CREDENTIALS` listed in `cleanup_env_keys`

```bash
php artisan test --filter=DeepLinkAndSyncStubTest
```

---

## What changed from Chapter 7

| File | Change |
| ---- | ------ |
| `.env.example` | `NATIVEPHP_DEEPLINK_SCHEME`, `NATIVEPHP_DEEPLINK_HOST` |
| `app/Support/NoteDeepLink.php` | **New** — scheme URL + push payload |
| `app/Http/Controllers/NoteController.php` | `show()`, sync dispatch |
| `resources/views/notes/show.blade.php` | **New** — read-only note view |
| `app/Jobs/SyncNoteToApi.php` | **New** — background sync stub |
| `app/Livewire/PushEnrollment.php` | **New** — Settings enroll UI |
| `app/Listeners/StorePushToken.php` | **New** |
| `database/migrations/*_device_push_tokens*` | **New** |
| `database/migrations/*_sync_pending*` | **New** |
| `routes/console.php` | `field-notes:push-payload` command |

---

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| Link opens browser | Re-run `native:install --force`; confirm scheme in `.env` |
| Push never arrives | Real Firebase files in project root; test on device |
| Wrong note | Payload `note_id` / `--url` must match `/notes/{id}` |
| Sync never clears | Confirm `jobs` table exists and `QUEUE_CONNECTION=database` |

---

## Remember

Chapter 9 ships the release candidate — see [Chapter 9 — Release candidate](../../9-deployment-and-store-submission/chapter-project-release-candidate/).

← [Field Notes capstone index](../../CAPSTONE.md)
