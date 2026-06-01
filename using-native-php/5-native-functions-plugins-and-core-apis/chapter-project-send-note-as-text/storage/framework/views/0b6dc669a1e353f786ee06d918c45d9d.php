<?php $__env->startSection('title', 'Compose — Field Notes'); ?>
<?php $__env->startSection('top_title', 'Compose a note'); ?>
<?php $__env->startSection('top_subtitle', 'Send as text'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-md px-5 pt-4">
        <?php if(session('status')): ?>
            <p class="mb-4 rounded-lg border border-sky-800/60 bg-sky-950/40 px-3 py-2 text-sm text-sky-300" role="status">
                <?php echo e(session('status')); ?>

            </p>
        <?php endif; ?>

        <p class="mb-4 text-sm leading-relaxed text-slate-400">
            Type a note and tap <strong class="font-medium text-slate-300">Send as text</strong> to open the system share sheet (Messages, Mail, and more). No database yet — Chapter 6 saves notes locally.
        </p>

        <form method="post" action="<?php echo e(route('compose.send')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <label class="block text-sm text-slate-400" for="body">Message</label>
            <textarea
                id="body"
                name="body"
                rows="6"
                required
                class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-slate-100 placeholder:text-slate-500"
                placeholder="Type a note to send as a text…"
            ><?php echo e(old('body')); ?></textarea>
            <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-sm text-red-400"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <button
                type="submit"
                class="w-full rounded-lg bg-sky-600 py-3 font-medium text-white active:bg-sky-700"
            >
                Send as text
            </button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/5-native-functions-plugins-and-core-apis/chapter-project-send-note-as-text/resources/views/compose.blade.php ENDPATH**/ ?>