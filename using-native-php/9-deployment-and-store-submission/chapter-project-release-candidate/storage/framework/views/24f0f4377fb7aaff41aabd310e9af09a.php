<?php $__env->startSection('title', 'Home — Field Notes'); ?>
<?php $__env->startSection('top_title', 'Field Notes'); ?>
<?php $__env->startSection('top_subtitle', 'Your notes'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto flex max-w-md flex-col px-5 pt-4">
        <p class="mb-4 text-sm leading-relaxed text-slate-400">
            Saved locally in SQLite — tap a note to open it. Push alerts deep-link to the same screen.
        </p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notes->isEmpty()): ?>
            <div class="rounded-xl border border-dashed border-slate-600 bg-slate-800/40 p-6 text-center">
                <p class="text-slate-300">No notes yet.</p>
                <p class="mt-2 text-sm text-slate-500">Open <strong class="text-slate-400">Compose</strong> to create your first note.</p>
            </div>
        <?php else: ?>
            <section class="space-y-3" aria-label="Notes">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="rounded-xl border border-slate-700/80 bg-slate-800/60 p-4">
                        <a href="<?php echo e(route('notes.show', $note)); ?>" class="block active:opacity-80">
                            <h2 class="text-base font-medium text-slate-100"><?php echo e($note->title); ?></h2>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-400"><?php echo e($note->body); ?></p>
                            <p class="mt-2 text-xs text-slate-500">
                                <?php echo e($note->updated_at->diffForHumans()); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($note->sync_pending): ?>
                                    · <span class="text-amber-500/80">Sync pending</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                        </a>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/9-deployment-and-store-submission/chapter-project-release-candidate/resources/views/notes/index.blade.php ENDPATH**/ ?>