<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <title>Field Notes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="nativephp-safe-area min-h-screen bg-slate-900 text-slate-100 antialiased">
    <div class="mx-auto flex min-h-screen max-w-md flex-col px-5 pb-8 pt-6">
        <header class="mb-8">
            <p class="text-sm font-medium uppercase tracking-widest text-sky-400">Field Notes</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-50">Your notes</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-400">
                Offline-first capture for site visits. Real sync and CRUD arrive in chapter 6 — this screen is your branded shell.
            </p>
        </header>

        {{-- Note list UI shell (static placeholders — no database yet) --}}
        <section class="flex-1 space-y-3" aria-label="Notes preview">
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

        <footer class="mt-8 border-t border-slate-800 pt-4 text-center text-xs text-slate-500">
            Same Blade in browser, Jump, and simulator · icon &amp; splash in <code class="text-sky-400">public/</code>
        </footer>
    </div>
</body>
</html>
