<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Each style is a value object — same shape across brands. */
final class Style
{
    public function __construct(
        public readonly string $font,
        public readonly string $colour,
        public readonly string $fontSize,
    ) {}
    public function toCss(): string
    {
        return "font-family:{$this->font};color:{$this->colour};font-size:{$this->fontSize}";
    }
}

interface BrandFactory
{
    public function headerStyle(): Style;
    public function bodyStyle(): Style;
    public function footerStyle(): Style;
}

final class HalfShellBrandFactory implements BrandFactory
{
    public function headerStyle(): Style { return new Style('Inter',     '#1a1a1a', '32px'); }
    public function bodyStyle(): Style   { return new Style('Inter',     '#333',    '16px'); }
    public function footerStyle(): Style { return new Style('Inter',     '#666',    '12px'); }
}

final class AcademyBrandFactory implements BrandFactory
{
    public function headerStyle(): Style { return new Style('Lora',      '#0a3d62', '28px'); }
    public function bodyStyle(): Style   { return new Style('Source Sans','#222',   '15px'); }
    public function footerStyle(): Style { return new Style('Lora',      '#777',    '11px'); }
}

final class WelcomeEmail
{
    public function __construct(private readonly BrandFactory $brand) {}

    public function build(string $name): string
    {
        return implode("\n", [
            "<header style=\"{$this->brand->headerStyle()->toCss()}\">Welcome, {$name}!</header>",
            "<section style=\"{$this->brand->bodyStyle()->toCss()}\">Thanks for signing up.</section>",
            "<footer style=\"{$this->brand->footerStyle()->toCss()}\">Sent with care.</footer>",
        ]);
    }
}

// ---- assertions -------------------------------------------------------------

$halfShell = new WelcomeEmail(new HalfShellBrandFactory());
$academy   = new WelcomeEmail(new AcademyBrandFactory());

$h = $halfShell->build('Sam');
pdp_assert_true(str_contains($h, 'font-family:Inter'),  'half shell uses Inter');
pdp_assert_true(str_contains($h, 'color:#1a1a1a'),      'half shell uses dark grey');
pdp_assert_true(str_contains($h, 'Welcome, Sam!'),      'name interpolated');

$a = $academy->build('Sam');
pdp_assert_true(str_contains($a, 'font-family:Lora'),       'academy uses Lora');
pdp_assert_true(str_contains($a, 'color:#0a3d62'),          'academy uses navy');
pdp_assert_true(str_contains($a, 'font-family:Source Sans'),'academy body uses Source Sans');

// adding a new brand is one new factory class, not template edits
$mono = new class implements BrandFactory {
    public function headerStyle(): Style { return new Style('Courier', '#000', '20px'); }
    public function bodyStyle(): Style   { return new Style('Courier', '#000', '14px'); }
    public function footerStyle(): Style { return new Style('Courier', '#888', '10px'); }
};
$m = (new WelcomeEmail($mono))->build('Sam');
pdp_assert_true(str_contains($m, 'font-family:Courier'), 'new brand requires zero changes to WelcomeEmail');

pdp_done();
