@extends('layouts.app')

@section('title', ($note->exists ? 'Edit' : 'Compose') . ' — Field Notes')
@section('top_title', $note->exists ? 'Edit note' : 'Compose a note')
@section('top_subtitle', $note->exists ? $note->title : 'New note')

@section('content')
    <div class="mx-auto max-w-md px-5 pt-4">
        @if (session('status'))
            <p class="mb-4 rounded-lg border border-sky-800/60 bg-sky-950/40 px-3 py-2 text-sm text-sky-300" role="status">
                {{ session('status') }}
            </p>
        @endif

        <form
            method="post"
            action="{{ $note->exists ? route('notes.update', $note) : route('notes.store') }}"
            class="space-y-4"
        >
            @csrf
            @if ($note->exists)
                @method('PUT')
            @endif

            <label class="block text-sm text-slate-400" for="title">Title</label>
            <input
                id="title"
                name="title"
                type="text"
                required
                maxlength="120"
                value="{{ old('title', $note->title) }}"
                class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-slate-100 placeholder:text-slate-500"
                placeholder="Site visit summary"
            />
            @error('title')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror

            <label class="block text-sm text-slate-400" for="body">Body</label>
            <textarea
                id="body"
                name="body"
                rows="8"
                required
                class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-slate-100 placeholder:text-slate-500"
                placeholder="What did you observe on site?"
            >{{ old('body', $note->body) }}</textarea>
            @error('body')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="w-full rounded-lg bg-sky-600 py-3 font-medium text-white active:bg-sky-700"
            >
                {{ $note->exists ? 'Save changes' : 'Save note' }}
            </button>
        </form>

        @if ($note->exists)
            <form method="post" action="{{ route('notes.share', $note) }}" class="mt-3">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-lg border border-slate-600 py-3 font-medium text-slate-200 active:bg-slate-800"
                >
                    Send as text
                </button>
            </form>

            <form
                method="post"
                action="{{ route('notes.destroy', $note) }}"
                class="mt-3"
                onsubmit="return confirm('Delete this note?');"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="w-full rounded-lg border border-red-900/60 py-3 font-medium text-red-400 active:bg-red-950/40"
                >
                    Delete note
                </button>
            </form>
        @endif
    </div>
@endsection
