@extends('layouts.app')

@section('title', 'Home — Field Notes')
@section('top_title', 'Field Notes')
@section('top_subtitle', 'Your notes')

@section('content')
    <div class="mx-auto flex max-w-md flex-col px-5 pt-4">
        <p class="mb-4 text-sm leading-relaxed text-slate-400">
            Saved locally in SQLite — works offline after the first load. Tap a note to edit or delete.
        </p>

        @if ($notes->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-600 bg-slate-800/40 p-6 text-center">
                <p class="text-slate-300">No notes yet.</p>
                <p class="mt-2 text-sm text-slate-500">Open <strong class="text-slate-400">Compose</strong> to create your first note.</p>
            </div>
        @else
            <section class="space-y-3" aria-label="Notes">
                @foreach ($notes as $note)
                    <article class="rounded-xl border border-slate-700/80 bg-slate-800/60 p-4">
                        <a href="{{ route('notes.edit', $note) }}" class="block active:opacity-80">
                            <h2 class="text-base font-medium text-slate-100">{{ $note->title }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-400">{{ $note->body }}</p>
                            <p class="mt-2 text-xs text-slate-500">{{ $note->updated_at->diffForHumans() }}</p>
                        </a>
                    </article>
                @endforeach
            </section>
        @endif
    </div>
@endsection
