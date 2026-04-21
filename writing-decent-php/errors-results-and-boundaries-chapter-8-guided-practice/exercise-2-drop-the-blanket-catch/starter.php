<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';
require_once __DIR__ . '/support/driver.php';

final class CreateInvoiceController
{
    public function __construct(private CreateInvoice $createInvoice) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $invoiceId = $this->createInvoice->handle($request);

            return new JsonResponse(['status' => 'ok', 'invoice_id' => $invoiceId], 201);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return new JsonResponse(['error' => 'Something went wrong'], 500);
        }
    }
}

runScenarios(new CreateInvoiceController(new CreateInvoice()));
