<?php
declare(strict_types=1);

final class InvoicePdfOptions
{
    public function __construct(
        public readonly bool $includeLogo        = false,
        public readonly bool $includeQrCode      = false,
        public readonly bool $includeBankDetails = false,
    ) {
    }

    public static function none(): self
    {
        return new self();
    }

    public static function logoOnly(): self
    {
        return new self(includeLogo: true);
    }

    public static function full(): self
    {
        return new self(includeLogo: true, includeQrCode: true, includeBankDetails: true);
    }
}

final class InvoicePdfRenderer
{
    public function generateFinalInvoicePdf(
        int               $invoiceId,
        string            $template,
        InvoicePdfOptions $options,
        string            $locale = 'en-GB',
    ): string {
        return $this->render($invoiceId, $template, $options, watermarkAsDraft: false, locale: $locale);
    }

    public function generateDraftInvoicePdf(
        int               $invoiceId,
        string            $template,
        InvoicePdfOptions $options,
        string            $locale = 'en-GB',
    ): string {
        return $this->render($invoiceId, $template, $options, watermarkAsDraft: true, locale: $locale);
    }

    private function render(
        int               $invoiceId,
        string            $template,
        InvoicePdfOptions $options,
        bool              $watermarkAsDraft,
        string            $locale,
    ): string {
        return "PDF(" . json_encode([
            'invoice'         => $invoiceId,
            'template'        => $template,
            'logo'            => $options->includeLogo,
            'qr'              => $options->includeQrCode,
            'bank'            => $options->includeBankDetails,
            'draft'           => $watermarkAsDraft,
            'locale'          => $locale,
            'emailToCustomer' => false,
        ]) . ")";
    }
}

final class InvoiceMailer
{
    /** @var list<array{invoiceId:int, pdfBytes:string}> */
    public array $sent = [];

    public function emailInvoiceToCustomer(int $invoiceId, string $pdfBytes): void
    {
        $this->sent[] = ['invoiceId' => $invoiceId, 'pdfBytes' => $pdfBytes];
    }
}

$renderer = new InvoicePdfRenderer();
$mailer   = new InvoiceMailer();

echo $renderer->generateFinalInvoicePdf(42, 'standard', InvoicePdfOptions::full())                       . "\n";
echo $renderer->generateDraftInvoicePdf(42, 'standard', InvoicePdfOptions::logoOnly())                   . "\n";
echo $renderer->generateFinalInvoicePdf(99, 'minimal',  InvoicePdfOptions::none(), 'fr-FR')              . "\n";

$pdfBytes = $renderer->generateFinalInvoicePdf(99, 'minimal', InvoicePdfOptions::full(), 'en-GB');
echo $pdfBytes . "\n";
$mailer->emailInvoiceToCustomer(99, $pdfBytes);

echo "emails: " . count($mailer->sent) . "\n";
