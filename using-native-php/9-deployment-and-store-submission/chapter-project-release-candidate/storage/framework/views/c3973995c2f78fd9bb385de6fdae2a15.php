<?php $__env->startSection('title', ($note->exists ? 'Edit' : 'Compose') . ' — Field Notes'); ?>
<?php $__env->startSection('top_title', $note->exists ? 'Edit note' : 'Compose a note'); ?>
<?php $__env->startSection('top_subtitle', $note->exists ? $note->title : 'New note'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-md px-5 pt-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <p class="mb-4 rounded-lg border border-sky-800/60 bg-sky-950/40 px-3 py-2 text-sm text-sky-300" role="status">
                <?php echo e(session('status')); ?>

            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form
            method="post"
            action="<?php echo e($note->exists ? route('notes.update', $note) : route('notes.store')); ?>"
            class="space-y-4"
        >
            <?php echo csrf_field(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($note->exists): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <label class="block text-sm text-slate-400" for="title">Title</label>
            <input
                id="title"
                name="title"
                type="text"
                required
                maxlength="120"
                value="<?php echo e(old('title', $note->title)); ?>"
                class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-slate-100 placeholder:text-slate-500"
                placeholder="Site visit summary"
            />
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-sm text-red-400"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <label class="block text-sm text-slate-400" for="body">Body</label>
            <textarea
                id="body"
                name="body"
                rows="8"
                required
                class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-slate-100 placeholder:text-slate-500"
                placeholder="What did you observe on site?"
            ><?php echo e(old('body', $note->body)); ?></textarea>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-sm text-red-400"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button
                type="submit"
                class="w-full rounded-lg bg-sky-600 py-3 font-medium text-white active:bg-sky-700"
            >
                <?php echo e($note->exists ? 'Save changes' : 'Save note'); ?>

            </button>
        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($note->exists): ?>
            <form method="post" action="<?php echo e(route('notes.share', $note)); ?>" class="mt-3">
                <?php echo csrf_field(); ?>
                <button
                    type="submit"
                    class="w-full rounded-lg border border-slate-600 py-3 font-medium text-slate-200 active:bg-slate-800"
                >
                    Send as text
                </button>
            </form>

            <form
                method="post"
                action="<?php echo e(route('notes.destroy', $note)); ?>"
                class="mt-3"
                onsubmit="return confirm('Delete this note?');"
            >
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button
                    type="submit"
                    class="w-full rounded-lg border border-red-900/60 py-3 font-medium text-red-400 active:bg-red-950/40"
                >
                    Delete note
                </button>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/9-deployment-and-store-submission/chapter-project-release-candidate/resources/views/notes/form.blade.php ENDPATH**/ ?>