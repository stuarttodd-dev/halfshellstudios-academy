<?php $__env->startSection('title', $note->title . ' — Field Notes'); ?>
<?php $__env->startSection('top_title', $note->title); ?>
<?php $__env->startSection('top_subtitle', 'Note'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-md px-5 pt-4">
        <article class="rounded-xl border border-slate-700/80 bg-slate-800/60 p-4">
            <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-200"><?php echo e($note->body); ?></p>
            <p class="mt-4 text-xs text-slate-500">
                Updated <?php echo e($note->updated_at->diffForHumans()); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($note->sync_pending): ?>
                    · <span class="text-amber-500/90">Sync pending</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </article>

        <div class="mt-4 flex flex-col gap-3">
            <a
                href="<?php echo e(route('notes.edit', $note)); ?>"
                class="block w-full rounded-lg bg-sky-600 py-3 text-center font-medium text-white active:bg-sky-700"
            >
                Edit note
            </a>
        </div>

        <p class="mt-6 text-xs leading-relaxed text-slate-500">
            Push notifications open this screen via deep link
            <code class="text-sky-400"><?php echo e($deepLink); ?></code>
            (scheme <code class="text-sky-400"><?php echo e(config('nativephp.deeplink_scheme')); ?></code>).
        </p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/8-deep-links-push-and-background-sync/chapter-project-deep-link-sync-stub/resources/views/notes/show.blade.php ENDPATH**/ ?>