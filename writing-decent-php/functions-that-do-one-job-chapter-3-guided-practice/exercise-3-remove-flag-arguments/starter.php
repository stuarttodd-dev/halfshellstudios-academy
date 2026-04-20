<?php
declare(strict_types=1);

function generateInvoicePdf(
    int    $invoiceId,
    string $template,
    bool   $includeLogo,
    bool   $includeQrCode,
    bool   $includeBankDetails,
    bool   $watermarkAsDraft,
    string $locale          = 'en-GB',
    bool   $emailToCustomer = false,
): string {
    return "PDF(" . json_encode([
        'invoice'         => $invoiceId,
        'template'        => $template,
        'logo'            => $includeLogo,
        'qr'              => $includeQrCode,
        'bank'            => $includeBankDetails,
        'draft'           => $watermarkAsDraft,
        'locale'          => $locale,
        'emailToCustomer' => $emailToCustomer,
    ]) . ")";
}

echo generateInvoicePdf(42, 'standard', true, true,  true,  false)                       . "\n";
echo generateInvoicePdf(42, 'standard', true, false, false, true)                        . "\n";
echo generateInvoicePdf(99, 'minimal',  false, false, false, false, 'fr-FR')             . "\n";
echo generateInvoicePdf(99, 'minimal',  true,  true,  true,  false, 'en-GB', true)       . "\n";
