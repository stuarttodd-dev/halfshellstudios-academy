<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            abort(403, 'Unauthenticated');
        }
        if (! (bool) $user->is_subscribed) {
            abort(403, 'Active subscription required.');
        }

        return $next($request);
    }
}
