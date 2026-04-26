<?php

// Laravel 11+ — add inside bootstrap/app.php -> Application::configure(...)->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
//     $middleware->alias([
//         'subscribed' => \App\Http\Middleware\EnsureUserIsSubscribed::class,
//     ]);
// });

// Laravel 10 — register alias in app/Http/Kernel.php $routeMiddleware (or $middlewareAliases).
