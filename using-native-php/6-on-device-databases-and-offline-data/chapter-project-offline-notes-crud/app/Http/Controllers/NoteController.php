<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Native\Mobile\Facades\Share;

class NoteController extends Controller
{
    public function index(): View
    {
        return view('notes.index', [
            'notes' => Note::query()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('notes.form', ['note' => new Note]);
    }

    public function store(Request $request): RedirectResponse
    {
        Note::query()->create($request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string'],
        ]));

        return redirect()->route('notes.index');
    }

    public function edit(Note $note): View
    {
        return view('notes.form', compact('note'));
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $note->update($request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string'],
        ]));

        return redirect()->route('notes.index');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();

        return redirect()->route('notes.index');
    }

    public function share(Note $note): RedirectResponse
    {
        Share::file('Field Notes', $note->body, '');

        return redirect()
            ->route('notes.edit', $note)
            ->with('status', 'Share sheet opened.');
    }
}
