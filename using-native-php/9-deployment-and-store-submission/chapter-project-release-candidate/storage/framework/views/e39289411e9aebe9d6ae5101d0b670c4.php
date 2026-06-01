<?php $__env->startSection('title', 'Settings — Field Notes'); ?>
<?php $__env->startSection('top_title', 'Settings'); ?>
<?php $__env->startSection('top_subtitle', 'Security'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-md px-5 pt-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <p class="mb-4 rounded-lg border border-sky-800/60 bg-sky-950/40 px-3 py-2 text-sm text-sky-300" role="status">
                <?php echo e(session('status')); ?>

            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <ul class="divide-y divide-slate-800 rounded-xl border border-slate-700/80 bg-slate-800/40">
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">App</span>
                <span class="text-sm text-slate-500">Field Notes</span>
            </li>
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">Bundle ID</span>
                <span class="text-xs text-slate-500"><?php echo e(config('nativephp.app_id') ?: env('NATIVEPHP_APP_ID', '—')); ?></span>
            </li>
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">Version</span>
                <span class="text-sm text-slate-500"><?php echo e($appVersion); ?> (<?php echo e($versionCode); ?>)</span>
            </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($privacyPolicyUrl): ?>
                <li class="flex items-center justify-between px-4 py-3">
                    <span class="text-sm text-slate-300">Privacy policy</span>
                    <a href="<?php echo e($privacyPolicyUrl); ?>" class="text-xs text-sky-400 underline-offset-2 hover:underline" target="_blank" rel="noopener">View</a>
                </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>

        <form method="post" action="<?php echo e(route('settings.lock')); ?>" class="mt-6 rounded-xl border border-slate-700/80 bg-slate-800/40 p-4">
            <?php echo csrf_field(); ?>
            <label class="flex items-center justify-between gap-4">
                <span>
                    <span class="block text-sm font-medium text-slate-200">Biometric lock</span>
                    <span class="mt-1 block text-xs text-slate-500">Require Face ID / fingerprint before showing notes</span>
                </span>
                <input
                    type="checkbox"
                    name="lock_enabled"
                    value="1"
                    <?php if($lockEnabled): echo 'checked'; endif; ?>
                    class="h-5 w-5 rounded border-slate-600 bg-slate-800 text-sky-600 focus:ring-sky-500"
                    onchange="this.form.submit()"
                />
            </label>
        </form>

        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('push-enrollment');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2876607145-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <p class="mt-4 text-xs leading-relaxed text-slate-500">
            Set <code class="text-sky-400">FIELD_NOTES_LOCK_ENABLED=true</code> in <code class="text-sky-400">.env</code> for device builds. Review <code class="text-sky-400">cleanup_env_keys</code> in <code class="text-sky-400">config/nativephp.php</code> before store submission.
        </p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/9-deployment-and-store-submission/chapter-project-release-candidate/resources/views/settings.blade.php ENDPATH**/ ?>