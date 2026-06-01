<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <title>Locked — Field Notes</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="nativephp-safe-area min-h-screen bg-slate-900 text-slate-100 antialiased">
    <?php echo e($slot); ?>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/7-security-and-authentication/chapter-project-lock-field-notes/resources/views/layouts/locked.blade.php ENDPATH**/ ?>