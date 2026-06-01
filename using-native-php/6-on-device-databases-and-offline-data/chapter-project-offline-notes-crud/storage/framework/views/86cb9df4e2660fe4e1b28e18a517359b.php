<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <title><?php echo $__env->yieldContent('title', 'Field Notes'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="nativephp-safe-area min-h-screen bg-slate-900 text-slate-100 antialiased">
    <?php if (isset($component)) { $__componentOriginalab5e27ce086159146bb5be096e5d3727 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab5e27ce086159146bb5be096e5d3727 = $attributes; } ?>
<?php $component = Native\Mobile\Edge\Components\Navigation\TopBar::resolve(['title' => '@yield(\'top_title\', \'Field Notes\')','subtitle' => '@yield(\'top_subtitle\', \'\')','backgroundColor' => '#0f172a','textColor' => '#f1f5f9'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('native-top-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Native\Mobile\Edge\Components\Navigation\TopBar::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab5e27ce086159146bb5be096e5d3727)): ?>
<?php $attributes = $__attributesOriginalab5e27ce086159146bb5be096e5d3727; ?>
<?php unset($__attributesOriginalab5e27ce086159146bb5be096e5d3727); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab5e27ce086159146bb5be096e5d3727)): ?>
<?php $component = $__componentOriginalab5e27ce086159146bb5be096e5d3727; ?>
<?php unset($__componentOriginalab5e27ce086159146bb5be096e5d3727); ?>
<?php endif; ?>

    <main class="pb-20">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php if (isset($component)) { $__componentOriginal6529c4a028ae21e0663a0ef763485168 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6529c4a028ae21e0663a0ef763485168 = $attributes; } ?>
<?php $component = Native\Mobile\Edge\Components\Navigation\BottomNav::resolve(['labelVisibility' => 'labeled','dark' => true] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('native-bottom-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Native\Mobile\Edge\Components\Navigation\BottomNav::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php if (isset($component)) { $__componentOriginal65e79709cc5dd13bb051ccf5036e597d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65e79709cc5dd13bb051ccf5036e597d = $attributes; } ?>
<?php $component = Native\Mobile\Edge\Components\Navigation\BottomNavItem::resolve(['id' => 'home','icon' => 'home','label' => 'Home','url' => ''.e(route('notes.index')).'','active' => request()->routeIs('notes.index')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('native-bottom-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Native\Mobile\Edge\Components\Navigation\BottomNavItem::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65e79709cc5dd13bb051ccf5036e597d)): ?>
<?php $attributes = $__attributesOriginal65e79709cc5dd13bb051ccf5036e597d; ?>
<?php unset($__attributesOriginal65e79709cc5dd13bb051ccf5036e597d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65e79709cc5dd13bb051ccf5036e597d)): ?>
<?php $component = $__componentOriginal65e79709cc5dd13bb051ccf5036e597d; ?>
<?php unset($__componentOriginal65e79709cc5dd13bb051ccf5036e597d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal65e79709cc5dd13bb051ccf5036e597d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65e79709cc5dd13bb051ccf5036e597d = $attributes; } ?>
<?php $component = Native\Mobile\Edge\Components\Navigation\BottomNavItem::resolve(['id' => 'compose','icon' => 'edit','label' => 'Compose','url' => ''.e(route('notes.create')).'','active' => request()->routeIs('notes.create', 'notes.edit', 'notes.store', 'notes.update')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('native-bottom-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Native\Mobile\Edge\Components\Navigation\BottomNavItem::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65e79709cc5dd13bb051ccf5036e597d)): ?>
<?php $attributes = $__attributesOriginal65e79709cc5dd13bb051ccf5036e597d; ?>
<?php unset($__attributesOriginal65e79709cc5dd13bb051ccf5036e597d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65e79709cc5dd13bb051ccf5036e597d)): ?>
<?php $component = $__componentOriginal65e79709cc5dd13bb051ccf5036e597d; ?>
<?php unset($__componentOriginal65e79709cc5dd13bb051ccf5036e597d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal65e79709cc5dd13bb051ccf5036e597d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65e79709cc5dd13bb051ccf5036e597d = $attributes; } ?>
<?php $component = Native\Mobile\Edge\Components\Navigation\BottomNavItem::resolve(['id' => 'settings','icon' => 'settings','label' => 'Settings','url' => ''.e(route('settings')).'','active' => request()->routeIs('settings')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('native-bottom-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Native\Mobile\Edge\Components\Navigation\BottomNavItem::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65e79709cc5dd13bb051ccf5036e597d)): ?>
<?php $attributes = $__attributesOriginal65e79709cc5dd13bb051ccf5036e597d; ?>
<?php unset($__attributesOriginal65e79709cc5dd13bb051ccf5036e597d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65e79709cc5dd13bb051ccf5036e597d)): ?>
<?php $component = $__componentOriginal65e79709cc5dd13bb051ccf5036e597d; ?>
<?php unset($__componentOriginal65e79709cc5dd13bb051ccf5036e597d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6529c4a028ae21e0663a0ef763485168)): ?>
<?php $attributes = $__attributesOriginal6529c4a028ae21e0663a0ef763485168; ?>
<?php unset($__attributesOriginal6529c4a028ae21e0663a0ef763485168); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6529c4a028ae21e0663a0ef763485168)): ?>
<?php $component = $__componentOriginal6529c4a028ae21e0663a0ef763485168; ?>
<?php unset($__componentOriginal6529c4a028ae21e0663a0ef763485168); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH /Users/stuart/PhpstormProjects/halfshellstudios-academy/using-native-php/6-on-device-databases-and-offline-data/chapter-project-offline-notes-crud/resources/views/layouts/app.blade.php ENDPATH**/ ?>