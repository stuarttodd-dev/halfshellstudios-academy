<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireBiometricUnlock
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->lockEnabled()) {
            return $next($request);
        }

        if ($request->session()->get('unlocked')) {
            return $next($request);
        }

        if ($request->routeIs('locked', 'settings', 'settings.lock')) {
            return $next($request);
        }

        return redirect()->route('locked', [
            'redirect' => $request->fullUrl(),
        ]);
    }

    private function lockEnabled(): bool
    {
        return (bool) session(
            'lock_enabled',
            config('field-notes.lock_enabled'),
        );
    }
}
