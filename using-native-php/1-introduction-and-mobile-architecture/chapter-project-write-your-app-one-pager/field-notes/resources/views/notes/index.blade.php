@extends('layouts.app')

@section('title', 'Notes — '.config('app.name'))

@section('content')
    <h1>Field notes</h1>
    <p class="muted">Offline-first capture. Writes save locally with <code>sync_state = pending</code>.</p>

    <div class="card">
        <form method="post" action="{{ route('notes.store') }}">
            @csrf
            <label for="body" class="muted">New note</label>
            <textarea id="body" name="body" placeholder="What did you see on site?" required>{{ old('body') }}</textarea>
            @error('body')
                <p class="muted" style="color:#b91c1c">{{ $message }}</p>
            @enderror
            <p style="margin-top:0.75rem">
                <button type="submit">Save locally</button>
            </p>
        </form>
    </div>

    @forelse ($notes as $note)
        <article class="card">
            <p style="margin:0 0 0.5rem">{{ $note->body }}</p>
            <p class="muted" style="margin:0">
                <span @class([
                    'badge',
                    'badge--pending' => $note->sync_state === 'pending',
                    'badge--synced' => $note->sync_state === 'synced',
                    'badge--failed' => $note->sync_state === 'failed',
                ])>{{ $note->sync_state }}</span>
                · {{ $note->created_at->diffForHumans() }}
            </p>
            <form method="post" action="{{ route('notes.destroy', $note) }}" style="margin-top:0.75rem">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--ghost">Delete</button>
            </form>
        </article>
    @empty
        <p class="muted">No notes yet. Add one above — works without network.</p>
    @endforelse
@endsection
