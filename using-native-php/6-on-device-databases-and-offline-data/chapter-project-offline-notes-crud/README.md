# Chapter 6 — Field Notes: offline notes CRUD (SQLite)

**Capstone:** [All chapters](../../CAPSTONE.md) · [← Chapter 5](../../5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/) · **Chapter 6** · [Chapter 7 →](../../7-security-and-authentication/chapter-project-lock-field-notes/)

**Course:** [Chapter project: offline notes CRUD](https://php-apps.learnio.dev/learn/sections/chapter-on-device-databases-and-offline-data/chapter-project-offline-notes-crud)

**Previous:** [Chapter 5 — Send as text](../../5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/) · **Next:** [Chapter 7 — Biometric lock](../../7-security-and-authentication/chapter-project-lock-field-notes/)

**Built on:** [Chapter 5 — Send as text](../../5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/) · **You add:** SQLite `notes` table, Eloquent CRUD, Share from saved notes.

---

## What you ship

- SQLite via `DB_CONNECTION=sqlite` and `database/database.sqlite`
- `Note` model + migration (`title`, `body`, timestamps)
- `NoteController` — index, create, store, edit, update, destroy, share
- Home tab lists notes from SQLite; Compose tab creates/edits notes
- **Send as text** on edit uses `Share::file()` with `$note->body` (Chapter 5 upgrade)

Docs: [Databases on device](https://nativephp.com/docs/mobile/3/concepts/databases)

---

## Step 1 — Get the project

```bash
git clone https://github.com/stuarttodd-dev/halfshellstudios-academy.git
cd halfshellstudios-academy/using-native-php/6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud
chmod +x setup.sh
./setup.sh
```

`setup.sh` creates `database/database.sqlite`, runs migrations, and registers the Share plugin from Chapter 5.

---

## Step 2 — SQLite configuration

In `.env`:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Migrations run locally first — they bundle into mobile builds via `native:run`.

---

## Step 3 — Routes and EDGE tabs

| Route | Purpose |
| ----- | ------- |
| `GET /` | List notes (`notes.index`) |
| `GET /notes/create` | New note form (Compose tab) |
| `POST /notes` | Save new note |
| `GET /notes/{note}/edit` | Edit note |
| `PUT /notes/{note}` | Update note |
| `DELETE /notes/{note}` | Delete note |
| `POST /notes/{note}/share` | Open share sheet with saved body |

Bottom nav: **Home** → `/`, **Compose** → `/notes/create`.

---

## Step 4 — Verify locally

```bash
php artisan serve
```

Create notes in the browser — data persists in `database/database.sqlite`.

```bash
php artisan test --filter=OfflineNotesCrudTest
```

---

## Step 5 — Verify on device (offline)

```bash
php artisan native:install --force
php artisan native:run ios
```

Checklist:

- [ ] Create two notes on **Compose**, see them on **Home**
- [ ] Edit and delete a note
- [ ] Force-quit and reopen — notes still there
- [ ] Enable airplane mode — list and edit still work
- [ ] **Send as text** on edit opens the share sheet with saved body

---

## What changed from Chapter 5

| File | Change |
| ---- | ------ |
| `app/Models/Note.php` | **New** |
| `database/migrations/*_create_notes_table.php` | **New** |
| `app/Http/Controllers/NoteController.php` | **New** — CRUD + share |
| `resources/views/notes/index.blade.php` | **New** — Home list |
| `resources/views/notes/form.blade.php` | **New** — create/edit/delete/share |
| `resources/views/layouts/app.blade.php` | Tabs point to `notes.*` routes |
| `routes/web.php` | Notes CRUD; removed draft-only Compose routes |
| `ComposeController`, `compose.blade.php`, `home.blade.php` | **Removed** |
| `setup.sh` | `touch database.sqlite` + `migrate` |

Unchanged: icon/splash, EDGE shell, Share plugin dependency.

---

## If it goes wrong

| Symptom | Fix |
| ------- | --- |
| `no such table: notes` | Run `php artisan migrate` before `native:run` |
| Empty Home after creating notes | Confirm `DB_DATABASE` path; check SQLite file exists |
| Data lost on reinstall | Expected — on-device SQLite lives in the app container |
| Share does nothing in browser | Use `native:run` on simulator/device |

---

## Remember

Chapter 7 adds biometric app lock — see [Chapter 7 — Biometric lock](../../7-security-and-authentication/chapter-project-lock-field-notes/).

← [Field Notes capstone index](../../CAPSTONE.md)
