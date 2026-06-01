@extends('layouts.app')

@section('title', 'Compose — Field Notes')
@section('top_title', 'Compose a note')
@section('top_subtitle', 'Send as text')

@section('content')
    <div class="mx-auto max-w-md px-5 pt-4">
        @if (session('status'))
            <p class="mb-4 rounded-lg border border-sky-800/60 bg-sky-950/40 px-3 py-2 text-sm text-sky-300" role="status">
                {{ session('status') }}
            </p>
        @endif

        <p class="mb-4 text-sm leading-relaxed text-slate-400">
            Type a note and tap <strong class="font-medium text-slate-300">Send as text</strong> to open the system share sheet (Messages, Mail, and more). No database yet — Chapter 6 saves notes locally.
        </p>

        <form method="post" action="{{ route('compose.send') }}" class="space-y-4">
            @csrf
            <label class="block text-sm text-slate-400" for="body">Message</label>
            <textarea
                id="body"
                name="body"
                rows="6"
                required
                class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-slate-100 placeholder:text-slate-500"
                placeholder="Type a note to send as a text…"
            >{{ old('body') }}</textarea>
            @error('body')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror
            <button
                type="submit"
                class="w-full rounded-lg bg-sky-600 py-3 font-medium text-white active:bg-sky-700"
            >
                Send as text
            </button>
        </form>
    </div>
@endsection
