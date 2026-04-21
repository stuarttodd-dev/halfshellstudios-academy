<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';
require_once __DIR__ . '/support/driver.php';

/**
 * Catch only what we know how to translate. Everything else is, by
 * definition, a bug — and bugs deserve to surface to the framework's
 * top-level handler so they get logged with full context, alerted on,
 * and surfaced in error tracking. Swallowing them with `\Throwable`
 * loses the stack trace and turns every problem into the same opaque
 * "Something went wrong" 500.
 */
final class CreateInvoiceController
{
    public function __construct(private CreateInvoice $createInvoice) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $invoiceId = $this->createInvoice->handle($request);
        } catch (CustomerNotFoundException $e) {
            return new JsonResponse(['error' => 'customer_not_found',     'message' => $e->getMessage()], 404);
        } catch (OrderAlreadyInvoicedException $e) {
            return new JsonResponse(['error' => 'order_already_invoiced', 'message' => $e->getMessage()], 409);
        } catch (InvalidInvoiceInputException $e) {
            return new JsonResponse(['error' => 'invalid_input',          'message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['status' => 'ok', 'invoice_id' => $invoiceId], 201);
    }
}

runScenarios(new CreateInvoiceController(new CreateInvoice()));
