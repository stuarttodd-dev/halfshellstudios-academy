<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OfflineNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(): View
    {
        return view('notes.index', [
            'notes' => OfflineNote::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        OfflineNote::create($data + ['sync_state' => OfflineNote::SYNC_PENDING]);

        return back()->with('status', 'Note saved locally (sync pending).');
    }

    public function destroy(OfflineNote $note): RedirectResponse
    {
        $note->delete();

        return back()->with('status', 'Note deleted.');
    }
}
