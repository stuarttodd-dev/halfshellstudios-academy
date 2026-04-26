<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'App')</title>
</head>
<body>
    <header>
        <h1>@yield('title', 'App')</h1>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
