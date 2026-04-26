# Chapter 10 — Exercise: authentication (web) + feature tests

**Course page:** [Build a coherent authentication layer](http://127.0.0.1:38080/learn/sections/chapter-10-authentication/exercise-build-auth-system)

## Files

- `files/routes/auth.php` — guest group (register, login) + auth group (dashboard, logout) with throttling on POST credentials.
- `files/app/Http/Controllers/AuthController.php` — `register`, `login`, `logout` using the `User` model and `Auth` facade, `session()->regenerate()` on success.
- `files/app/Http/Requests/RegisterUserRequest.php`, `LoginUserRequest.php`
- `files/tests/Feature/AuthenticationTest.php` — guest blocked from dashboard, register path, login, wrong password, logout.

**Include** `require __DIR__.'/auth.php';` from `routes/web.php` (inside the `web` stack).

## Views

The lesson expects Blade forms with `@csrf` and `old('email')`. Generate minimal `resources/views/auth/login.blade.php` and `register.blade.php` in your app (not duplicated here) pointing `action` at named routes. Or assert JSON if you are building an API-only slice (then use Sanctum as the stretch goal).

## Commands

```bash
php artisan test --filter=AuthenticationTest
```
