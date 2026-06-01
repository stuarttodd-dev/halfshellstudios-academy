<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <title>@yield('title', 'Field Notes')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="nativephp-safe-area min-h-screen bg-slate-900 text-slate-100 antialiased">
    <native:top-bar
        title="@yield('top_title', 'Field Notes')"
        subtitle="@yield('top_subtitle', '')"
        background-color="#0f172a"
        text-color="#f1f5f9"
    />

    <main class="pb-20">
        @yield('content')
    </main>

    <native:bottom-nav label-visibility="labeled" dark>
        <native:bottom-nav-item
            id="home"
            icon="home"
            label="Home"
            url="{{ route('home') }}"
            :active="request()->routeIs('home')"
        />
        <native:bottom-nav-item
            id="compose"
            icon="edit"
            label="Compose"
            url="{{ route('compose') }}"
            :active="request()->routeIs('compose')"
        />
        <native:bottom-nav-item
            id="settings"
            icon="settings"
            label="Settings"
            url="{{ route('settings') }}"
            :active="request()->routeIs('settings')"
        />
    </native:bottom-nav>
</body>
</html>
