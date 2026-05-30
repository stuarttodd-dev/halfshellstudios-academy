# Chapter project: write your app one-pager (features, plugins, platforms)

| Lesson | Use |
| ------ | --- |
| [Chapter project: write your app one-pager](https://docker.learnio.dev/learn/sections/chapter-introduction-and-mobile-architecture/chapter-project-write-your-app-one-pager-features-plugins-platforms) | Copy [`app-one-pager.template.md`](app-one-pager.template.md) and fill it in yourself |
| Solution | [`app-one-pager.md`](app-one-pager.md) + [`field-notes/`](field-notes/) Laravel app |

## Exercise

1. Copy `app-one-pager.template.md` to your own file.
2. Fill in features, plugins, and platforms.
3. Compare to [`app-one-pager.md`](app-one-pager.md) and run the app below.

## Run the solution app

```bash
cd field-notes
chmod +x setup.sh
./setup.sh
php artisan serve --host=127.0.0.1 --port=8016
```

Open [http://127.0.0.1:8016/notes](http://127.0.0.1:8016/notes). Use **Activity** and **Settings** in the bottom nav.

```bash
php artisan test --filter=OfflineNoteTest
```

## NativePHP Mobile (chapter 3+)

```bash
cd field-notes
export NATIVEPHP_APP_ID=com.halfshell.fieldnotes
php artisan native:install
php artisan native:run
```

(`nativephp/mobile` is already in `field-notes/composer.json`.)

## What's here

| Item | Purpose |
| ---- | ------- |
| [`field-notes/`](field-notes/) | Laravel capstone starter — `OfflineNote` CRUD, `/notes` · `/activity` · `/settings` |
| [`app-one-pager.md`](app-one-pager.md) | Reference one-pager |
| [`app-one-pager.template.md`](app-one-pager.template.md) | Blank template |

← [Using Native PHP](../../README.md)
