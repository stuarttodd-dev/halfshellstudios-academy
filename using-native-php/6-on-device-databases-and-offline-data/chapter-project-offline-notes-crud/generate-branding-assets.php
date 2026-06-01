<?php

declare(strict_types=1);

/**
 * Regenerate public/icon.png, splash.png, splash-dark.png (lesson 3.8).
 * Requires PHP GD. Run: php generate-branding-assets.php
 */

$public = __DIR__.'/public';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "PHP GD extension required.\n");
    exit(1);
}

function write_png(int $width, int $height, string $path, callable $paint): void
{
    $im = imagecreatetruecolor($width, $height);
    $paint($im, $width, $height);
    imagepng($im, $path);
    imagedestroy($im);
    echo "Wrote {$path}\n";
}

function rgb($im, int $r, int $g, int $b): int
{
    return imagecolorallocate($im, $r, $g, $b);
}

// Icon 1024×1024 — slate-900 + sky accent
write_png(1024, 1024, "{$public}/icon.png", function ($im, $w, $h): void {
    $bg = rgb($im, 15, 23, 42);
    imagefilledrectangle($im, 0, 0, $w, $h, $bg);
    $card = rgb($im, 30, 41, 59);
    $accent = rgb($im, 56, 189, 248);
    imagefilledrectangle($im, 112, 112, 912, 912, $card);
    imagerectangle($im, 112, 112, 912, 912, $accent);
    $white = rgb($im, 226, 232, 240);
    imagestring($im, 5, 430, 470, 'FN', $accent);
    imagestring($im, 3, 360, 560, 'Field Notes', $white);
});

$paintSplash = function (int $bgR, int $bgG, int $bgB) use ($public): void {
    write_png(1080, 1920, "{$public}/splash.png", function ($im, $w, $h) use ($bgR, $bgG, $bgB): void {
        $bg = rgb($im, $bgR, $bgG, $bgB);
        imagefilledrectangle($im, 0, 0, $w, $h, $bg);
        $title = rgb($im, 226, 232, 240);
        $sub = rgb($im, 56, 189, 248);
        imagestring($im, 5, 380, 880, 'Field Notes', $title);
        imagestring($im, 3, 300, 960, 'Offline notes for the field', $sub);
    });
};

$paintSplash(15, 23, 42);

write_png(1080, 1920, "{$public}/splash-dark.png", function ($im, $w, $h): void {
    $bg = rgb($im, 2, 6, 23);
    imagefilledrectangle($im, 0, 0, $w, $h, $bg);
    $title = rgb($im, 226, 232, 240);
    $sub = rgb($im, 56, 189, 248);
    imagestring($im, 5, 380, 880, 'Field Notes', $title);
    imagestring($im, 3, 300, 960, 'Offline notes for the field', $sub);
});

echo "Done. Run: php artisan native:install --force\n";
