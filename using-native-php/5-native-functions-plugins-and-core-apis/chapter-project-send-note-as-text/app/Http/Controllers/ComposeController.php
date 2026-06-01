<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Native\Mobile\Facades\Share;

class ComposeController extends Controller
{
    public function show(): View
    {
        return view('compose');
    }

    public function send(Request $request): RedirectResponse
    {
        $body = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ])['body'];

        Share::file('Field Notes', $body, '');

        return redirect()
            ->route('compose')
            ->with('status', 'Share sheet opened.');
    }
}
