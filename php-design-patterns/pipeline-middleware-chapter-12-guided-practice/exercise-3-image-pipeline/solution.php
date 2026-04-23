<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class Image
{
    /** @param list<string> $log */
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public readonly bool $hasExif = true,
        public readonly bool $watermarked = false,
        public readonly int $quality = 100,
        public readonly array $log = [],
    ) {}

    public function with(?int $width = null, ?int $height = null, ?bool $hasExif = null, ?bool $watermarked = null, ?int $quality = null, ?string $event = null): self
    {
        return new self(
            width: $width ?? $this->width,
            height: $height ?? $this->height,
            hasExif: $hasExif ?? $this->hasExif,
            watermarked: $watermarked ?? $this->watermarked,
            quality: $quality ?? $this->quality,
            log: $event === null ? $this->log : [...$this->log, $event],
        );
    }
}

interface ImageStep
{
    public function process(Image $image, callable $next): Image;
}

final class ImagePipeline
{
    /** @param list<ImageStep> $steps */
    public function __construct(private readonly array $steps) {}

    public function run(Image $image): Image
    {
        $core = static fn (Image $i): Image => $i;
        $next = $core;
        foreach (array_reverse($this->steps) as $step) {
            $current = $next;
            $next = static fn (Image $i): Image => $step->process($i, $current);
        }
        return $next($image);
    }
}

final class ResizeMax implements ImageStep
{
    public function __construct(private readonly int $maxWidth) {}
    public function process(Image $image, callable $next): Image
    {
        if ($image->width <= $this->maxWidth) return $next($image);
        $ratio = $this->maxWidth / $image->width;
        return $next($image->with(width: $this->maxWidth, height: (int) round($image->height * $ratio), event: "resize:{$this->maxWidth}"));
    }
}

final class StripExif implements ImageStep
{
    public function process(Image $image, callable $next): Image
    {
        return $next($image->with(hasExif: false, event: 'strip-exif'));
    }
}

final class Watermark implements ImageStep
{
    public function process(Image $image, callable $next): Image
    {
        return $next($image->with(watermarked: true, event: 'watermark'));
    }
}

final class Compress implements ImageStep
{
    public function __construct(private readonly int $quality) {}
    public function process(Image $image, callable $next): Image
    {
        return $next($image->with(quality: $this->quality, event: "compress:{$this->quality}"));
    }
}

// ---- wiring ----------------------------------------------------------------

$strip = new StripExif();
$watermark = new Watermark();

$fullSize = new ImagePipeline([
    new ResizeMax(4000),
    $strip,
    $watermark,
    new Compress(85),
]);

// thumbnails: smaller, no watermark, harder compression — same step library.
$thumbnail = new ImagePipeline([
    new ResizeMax(400),
    $strip,
    new Compress(70),
]);

// ---- assertions -------------------------------------------------------------

$big = new Image(width: 6000, height: 4000);
$out = $fullSize->run($big);
pdp_assert_eq(4000, $out->width, 'full-size resized down to 4000');
pdp_assert_eq(false, $out->hasExif, 'full-size strips exif');
pdp_assert_eq(true, $out->watermarked, 'full-size watermarked');
pdp_assert_eq(85, $out->quality, 'full-size compressed at 85');
pdp_assert_eq(['resize:4000', 'strip-exif', 'watermark', 'compress:85'], $out->log, 'steps ran in order');

$thumb = $thumbnail->run($big);
pdp_assert_eq(400, $thumb->width, 'thumbnail uses smaller resize');
pdp_assert_eq(false, $thumb->watermarked, 'thumbnail has no watermark step');
pdp_assert_eq(70, $thumb->quality, 'thumbnail compressed harder');
pdp_assert_eq(['resize:400', 'strip-exif', 'compress:70'], $thumb->log, 'thumbnail uses different composition of same steps');

$small = $fullSize->run(new Image(width: 800, height: 600));
pdp_assert_true(!in_array('resize:4000', $small->log, true), 'small image skipped resize step (guard inside step)');

pdp_done();
