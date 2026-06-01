<?php $__env->startSection('title', 'Settings — Field Notes'); ?>
<?php $__env->startSection('top_title', 'Settings'); ?>
<?php $__env->startSection('top_subtitle', 'Field Notes'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-md px-5 pt-4">
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
                <span class="text-sm text-slate-300">Biometric lock</span>
                <span class="text-sm text-slate-500">Chapter 7</span>
            </li>
        </ul>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/resources/views/settings.blade.php ENDPATH**/ ?>