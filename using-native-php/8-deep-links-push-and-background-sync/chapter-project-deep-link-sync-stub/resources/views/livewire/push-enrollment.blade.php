<div class="mt-6 rounded-xl border border-slate-700/80 bg-slate-800/40 p-4">
    <h2 class="text-sm font-medium text-slate-200">Push notifications</h2>
    <p class="mt-1 text-xs text-slate-500">
        Enroll for alerts that deep-link to a note. Requires Firebase config files and <code class="text-sky-400">native:run</code>.
    </p>

    <dl class="mt-3 space-y-2 text-xs">
        <div class="flex justify-between gap-4">
            <dt class="text-slate-500">Permission</dt>
            <dd class="text-slate-300">{{ $permission ?? 'unknown' }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-slate-500">Token</dt>
            <dd class="font-mono text-slate-400">{{ $tokenPreview ?? 'Not enrolled' }}</dd>
        </div>
    </dl>

    <button
        type="button"
        wire:click="enroll"
        class="mt-4 w-full rounded-lg border border-slate-600 py-2.5 text-sm font-medium text-slate-200 active:bg-slate-800"
    >
        Enable push notifications
    </button>
</div>
