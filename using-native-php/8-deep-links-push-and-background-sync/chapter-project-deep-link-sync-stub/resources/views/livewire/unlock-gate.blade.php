<div class="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center px-5 text-center">
    <p class="text-sm font-medium uppercase tracking-widest text-sky-400">Field Notes</p>
    <h1 class="mt-2 text-2xl font-semibold text-slate-50">Unlock to continue</h1>
    <p class="mt-3 text-sm leading-relaxed text-slate-400">
        Use Face ID, Touch ID, fingerprint, or your device passcode to view your notes.
    </p>

    @if ($failed)
        <p class="mt-6 rounded-lg border border-red-900/60 bg-red-950/30 px-4 py-3 text-sm text-red-300" role="alert">
            Authentication failed or was cancelled. Try again.
        </p>
    @endif

    <button
        type="button"
        wire:click="requestUnlock"
        class="mt-8 w-full rounded-lg bg-sky-600 py-3 font-medium text-white active:bg-sky-700"
    >
        Try again
    </button>

    <a href="{{ route('settings') }}" class="mt-4 text-sm text-slate-500 underline-offset-2 hover:text-slate-300 hover:underline">
        Settings
    </a>
</div>
