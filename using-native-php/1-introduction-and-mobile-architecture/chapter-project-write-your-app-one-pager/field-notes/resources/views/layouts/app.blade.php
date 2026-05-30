<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', config('app.name'))</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #f4f4f5;
            --card: #fff;
            --text: #18181b;
            --muted: #71717a;
            --accent: #2563eb;
            --border: #e4e4e7;
            --nav-h: 4.25rem;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #09090b;
                --card: #18181b;
                --text: #fafafa;
                --muted: #a1a1aa;
                --border: #27272a;
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100dvh;
            padding-bottom: calc(var(--nav-h) + env(safe-area-inset-bottom));
        }
        main {
            max-width: 40rem;
            margin: 0 auto;
            padding: 1rem 1rem 1.5rem;
        }
        h1 { font-size: 1.35rem; margin: 0 0 0.75rem; }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
        }
        .muted { color: var(--muted); font-size: 0.875rem; }
        .badge {
            display: inline-block;
            font-size: 0.75rem;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            background: var(--border);
        }
        .badge--pending { background: #fef3c7; color: #92400e; }
        .badge--synced { background: #dcfce7; color: #166534; }
        .badge--failed { background: #fee2e2; color: #991b1b; }
        .flash {
            background: #dbeafe;
            color: #1e40af;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        form textarea {
            width: 100%;
            min-height: 5rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.75rem;
            font: inherit;
            background: var(--bg);
            color: var(--text);
        }
        button, .btn {
            appearance: none;
            border: none;
            border-radius: 0.5rem;
            padding: 0.65rem 1rem;
            font: inherit;
            cursor: pointer;
            background: var(--accent);
            color: #fff;
        }
        .btn--ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
        }
        nav.bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            height: var(--nav-h);
            padding-bottom: env(safe-area-inset-bottom);
            display: flex;
            background: var(--card);
            border-top: 1px solid var(--border);
        }
        nav.bottom-nav a {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 600;
        }
        nav.bottom-nav a.active { color: var(--accent); }
        ul.stats { list-style: none; padding: 0; margin: 0; }
        ul.stats li {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border);
        }
        table.plugins { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        table.plugins th, table.plugins td {
            text-align: left;
            padding: 0.5rem;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }
    </style>
</head>
<body>
    <main>
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <nav class="bottom-nav" aria-label="Main">
        <a href="{{ route('notes.index') }}" @class(['active' => request()->routeIs('notes.*')])>Notes</a>
        <a href="{{ route('activity.index') }}" @class(['active' => request()->routeIs('activity.*')])>Activity</a>
        <a href="{{ route('settings.index') }}" @class(['active' => request()->routeIs('settings.*')])>Settings</a>
    </nav>
</body>
</html>
