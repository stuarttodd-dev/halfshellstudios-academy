<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function register(RegisterUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        Auth::login($user);
        $request->session()->regenerate();

        // `redirect('/dashboard')` can emit `Location: http://host:port` only under `php artisan serve`; build the URL from the current request.
        return new RedirectResponse($request->getUriForPath('/dashboard'));
    }

    public function login(LoginUserRequest $request): RedirectResponse
    {
        $creds = $request->only('email', 'password');
        $remember = (bool) $request->boolean('remember');
        if (! Auth::attempt($creds, $remember)) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended');
        $target = $intended
            ? (is_string($intended) && str_starts_with($intended, 'http') ? $intended : $request->getUriForPath((string) $intended))
            : $request->getUriForPath('/dashboard');

        return new RedirectResponse($target);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return new RedirectResponse($request->getUriForPath('/login'));
    }
}
