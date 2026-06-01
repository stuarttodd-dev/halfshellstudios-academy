@extends('layouts.app')

@section('title', 'Settings — Field Notes')
@section('top_title', 'Settings')
@section('top_subtitle', 'Security')

@section('content')
    <div class="mx-auto max-w-md px-5 pt-4">
        @if (session('status'))
            <p class="mb-4 rounded-lg border border-sky-800/60 bg-sky-950/40 px-3 py-2 text-sm text-sky-300" role="status">
                {{ session('status') }}
            </p>
        @endif

        <ul class="divide-y divide-slate-800 rounded-xl border border-slate-700/80 bg-slate-800/40">
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">App</span>
                <span class="text-sm text-slate-500">Field Notes</span>
            </li>
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">Bundle ID</span>
                <span class="text-xs text-slate-500">{{ config('nativephp.app_id') ?: env('NATIVEPHP_APP_ID', '—') }}</span>
            </li>
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">Version</span>
                <span class="text-sm text-slate-500">{{ $appVersion }} ({{ $versionCode }})</span>
            </li>
            @if ($privacyPolicyUrl)
                <li class="flex items-center justify-between px-4 py-3">
                    <span class="text-sm text-slate-300">Privacy policy</span>
                    <a href="{{ $privacyPolicyUrl }}" class="text-xs text-sky-400 underline-offset-2 hover:underline" target="_blank" rel="noopener">View</a>
                </li>
            @endif
        </ul>

        <form method="post" action="{{ route('settings.lock') }}" class="mt-6 rounded-xl border border-slate-700/80 bg-slate-800/40 p-4">
            @csrf
            <label class="flex items-center justify-between gap-4">
                <span>
                    <span class="block text-sm font-medium text-slate-200">Biometric lock</span>
                    <span class="mt-1 block text-xs text-slate-500">Require Face ID / fingerprint before showing notes</span>
                </span>
                <input
                    type="checkbox"
                    name="lock_enabled"
                    value="1"
                    @checked($lockEnabled)
                    class="h-5 w-5 rounded border-slate-600 bg-slate-800 text-sky-600 focus:ring-sky-500"
                    onchange="this.form.submit()"
                />
            </label>
        </form>

        @livewire('push-enrollment')

        <p class="mt-4 text-xs leading-relaxed text-slate-500">
            Set <code class="text-sky-400">FIELD_NOTES_LOCK_ENABLED=true</code> in <code class="text-sky-400">.env</code> for device builds. Review <code class="text-sky-400">cleanup_env_keys</code> in <code class="text-sky-400">config/nativephp.php</code> before store submission.
        </p>
    </div>
@endsection
