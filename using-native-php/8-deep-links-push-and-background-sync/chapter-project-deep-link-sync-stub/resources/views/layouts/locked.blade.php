<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <title>Locked — Field Notes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="nativephp-safe-area min-h-screen bg-slate-900 text-slate-100 antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
