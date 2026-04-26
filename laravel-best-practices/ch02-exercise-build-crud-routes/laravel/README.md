# Exercise app (chapter 2)

This folder **is** the chapter solution. Open these if you are hunting for the reference implementation:

| What | Path (under this `laravel/` folder) |
| --- | --- |
| **Controller (CRUD JSON)** | `app/Http/Controllers/ProductController.php` |
| **Model** | `app/Models/Product.php` |
| **Product routes** | `routes/products.php` (loaded by `routes/solution.php`) |
| **Migration** | `database/migrations/0001_01_01_000003_create_products_table.php` |
| **Boot** | `routes/web.php` → requires `routes/solution.php` → requires `products.php` |

A parallel copy of the same paths for reading only lives in the parent folder’s `files/`. From the parent folder, `rsync -a files/ laravel/` matches them (see the [main README](../../README.md#setup-one-chapter-app)).

**Dev port `8002`:** `http://127.0.0.1:8002` — `php artisan serve --host=127.0.0.1 --port=8002`

- **Run & test guide:** [../README.md](../README.md)
- **All chapters:** [../../README.md](../../README.md)
- `APP_URL` in `.env.example` is set to this port for local URL generation.

