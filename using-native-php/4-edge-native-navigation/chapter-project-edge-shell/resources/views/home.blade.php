@extends('layouts.app')

@section('title', 'Home — Field Notes')
@section('top_title', 'Field Notes')
@section('top_subtitle', 'Home')

@section('content')
    <div class="mx-auto flex max-w-md flex-col px-5 pt-4">
        <p class="mb-6 text-sm leading-relaxed text-slate-400">
            Offline-first capture for site visits. Real sync and CRUD arrive in chapter 6 — placeholder rows below.
        </p>

        <section class="space-y-3" aria-label="Notes preview">
            @foreach ([
                ['title' => 'Transformer pad inspection', 'meta' => 'Draft · syncs in ch. 6'],
                ['title' => 'Cable run photos', 'meta' => 'Placeholder row'],
                ['title' => 'Client sign-off', 'meta' => 'Placeholder row'],
            ] as $note)
                <article class="rounded-xl border border-slate-700/80 bg-slate-800/60 p-4 active:bg-slate-800">
                    <h2 class="text-base font-medium text-slate-100">{{ $note['title'] }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $note['meta'] }}</p>
                </article>
            @endforeach
        </section>
    </div>
@endsection
