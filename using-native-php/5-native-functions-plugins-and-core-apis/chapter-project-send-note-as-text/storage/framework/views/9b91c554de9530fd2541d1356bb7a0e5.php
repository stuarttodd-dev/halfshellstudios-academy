<?php $__env->startSection('title', 'Home — Field Notes'); ?>
<?php $__env->startSection('top_title', 'Field Notes'); ?>
<?php $__env->startSection('top_subtitle', 'Home'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto flex max-w-md flex-col px-5 pt-4">
        <p class="mb-6 text-sm leading-relaxed text-slate-400">
            Offline-first capture for site visits. Real sync and CRUD arrive in chapter 6 — placeholder rows below.
        </p>

        <section class="space-y-3" aria-label="Notes preview">
            <?php $__currentLoopData = [
                ['title' => 'Transformer pad inspection', 'meta' => 'Draft · syncs in ch. 6'],
                ['title' => 'Cable run photos', 'meta' => 'Placeholder row'],
                ['title' => 'Client sign-off', 'meta' => 'Placeholder row'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="rounded-xl border border-slate-700/80 bg-slate-800/60 p-4 active:bg-slate-800">
                    <h2 class="text-base font-medium text-slate-100"><?php echo e($note['title']); ?></h2>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e($note['meta']); ?></p>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/resources/views/home.blade.php ENDPATH**/ ?>