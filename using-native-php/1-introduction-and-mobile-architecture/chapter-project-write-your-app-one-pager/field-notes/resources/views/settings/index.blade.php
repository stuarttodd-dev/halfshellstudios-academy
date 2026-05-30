@extends('layouts.app')

@section('title', 'Settings — '.config('app.name'))

@section('content')
    <h1>Settings</h1>
    <p class="muted">Capstone identity and plugin plan from your one-pager.</p>

    <div class="card">
        <p style="margin:0"><strong>App name:</strong> {{ $appName }}</p>
        <p class="muted" style="margin:0.5rem 0 0">Bundle ID and version land in chapter 4 (`NATIVEPHP_APP_ID`).</p>
    </div>

    <h2 style="font-size:1rem;margin:1.25rem 0 0.5rem">Plugins (v1 plan)</h2>
    <div class="card" style="padding:0;overflow:auto">
        <table class="plugins">
            <thead>
                <tr>
                    <th>Package</th>
                    <th>Type</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plugins as $plugin)
                    <tr>
                        <td><code>{{ $plugin['package'] }}</code></td>
                        <td>{{ $plugin['type'] }}</td>
                        <td>{{ $plugin['purpose'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2 style="font-size:1rem;margin:1.25rem 0 0.5rem">Platforms</h2>
    <div class="card">
        <ul class="stats">
            @foreach ($platforms as $platform)
                <li>
                    <span>{{ $platform['name'] }}</span>
                    <strong>{{ ($platform['ship'] ?? false) ? 'Ship v1' : 'Dev only' }}</strong>
                </li>
            @endforeach
        </ul>
        @isset($platforms[2]['note'])
            <p class="muted" style="margin:0.75rem 0 0">{{ $platforms[2]['note'] }}</p>
        @endisset
    </div>
@endsection
