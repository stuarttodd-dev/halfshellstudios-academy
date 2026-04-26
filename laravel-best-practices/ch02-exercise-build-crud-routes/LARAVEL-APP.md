# Runnable Laravel app (chapter 2)

From `laravel-best-practices/`:

```bash
cd ch02-exercise-build-crud-routes
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8002
```

- Health: <http://127.0.0.1:8002/exercise>
- Products: <http://127.0.0.1:8002/products> (after `php artisan migrate`)

The solution is maintained under **`laravel/`** in this exercise folder. See this folder’s [README](README.md) for what to test.
