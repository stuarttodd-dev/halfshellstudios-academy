# Runnable Laravel app (chapter 2)

```bash
cd laravel
cp -n .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite   # or rely on default sqlite path in .env
php artisan migrate
php artisan serve
# GET http://127.0.0.1:8000/exercise
# GET http://127.0.0.1:8000/products
```

`routes/solution.php` is merged by the materialize script; `files/` in the parent directory is the source of truth to edit before re-running `php ../scripts/materialize_laravel_apps.php` (maintainers only).
