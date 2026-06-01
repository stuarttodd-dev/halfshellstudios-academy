@extends('layouts.app')

@section('title', $note->title . ' — Field Notes')
@section('top_title', $note->title)
@section('top_subtitle', 'Note')

@section('content')
    <div class="mx-auto max-w-md px-5 pt-4">
        <article class="rounded-xl border border-slate-700/80 bg-slate-800/60 p-4">
            <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-200">{{ $note->body }}</p>
            <p class="mt-4 text-xs text-slate-500">
                Updated {{ $note->updated_at->diffForHumans() }}
                @if ($note->sync_pending)
                    · <span class="text-amber-500/90">Sync pending</span>
                @endif
            </p>
        </article>

        <div class="mt-4 flex flex-col gap-3">
            <a
                href="{{ route('notes.edit', $note) }}"
                class="block w-full rounded-lg bg-sky-600 py-3 text-center font-medium text-white active:bg-sky-700"
            >
                Edit note
            </a>
        </div>

        <p class="mt-6 text-xs leading-relaxed text-slate-500">
            Push notifications open this screen via deep link
            <code class="text-sky-400">{{ $deepLink }}</code>
            (scheme <code class="text-sky-400">{{ config('nativephp.deeplink_scheme') }}</code>).
        </p>
    </div>
@endsection
