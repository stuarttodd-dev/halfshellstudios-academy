<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Validation lives here, not in the controller. The constructor enforces
 * the invariants; the named factory parses an HTTP request into a typed
 * input. Anything past this point only ever sees a valid input.
 */
final class RescheduleBookingInput
{
    private const MAX_REASON_LENGTH = 500;

    public function __construct(
        public readonly int    $bookingId,
        public readonly string $newDate,
        public readonly string $reason,
    ) {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
            throw new InvalidInputException('new_date must be YYYY-MM-DD');
        }

        if (strlen($reason) > self::MAX_REASON_LENGTH) {
            throw new InvalidInputException('reason too long');
        }
    }

    public static function fromRequest(Request $request, int $bookingId): self
    {
        return new self(
            bookingId: $bookingId,
            newDate:   (string) ($request->input('new_date') ?? ''),
            reason:    trim((string) $request->input('reason', '')),
        );
    }
}

final class InvalidInputException extends RuntimeException {}

/**
 * The use case knows nothing about HTTP — it takes a typed input and
 * performs the work.
 */
final class RescheduleBooking
{
    public function execute(RescheduleBookingInput $input): void
    {
        DB::table('bookings')->where('id', $input->bookingId)->update([
            'starts_on'         => $input->newDate,
            'reschedule_reason' => $input->reason,
        ]);
    }
}

/**
 * Eight lines of body, one job: parse → call → respond.
 */
final class RescheduleBookingController
{
    public function __construct(private RescheduleBooking $useCase) {}

    public function __invoke(Request $request, int $bookingId): JsonResponse
    {
        try {
            $input = RescheduleBookingInput::fromRequest($request, $bookingId);
        } catch (InvalidInputException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        $this->useCase->execute($input);

        return new JsonResponse(['status' => 'rescheduled']);
    }
}

/* ---------- driver (identical to starter.php) ---------- */

$controller = new RescheduleBookingController(new RescheduleBooking());

$cases = [
    'happy'             => new Request(['new_date' => '2026-05-01', 'reason' => 'on holiday']),
    'no reason given'   => new Request(['new_date' => '2026-05-02']),
    'whitespace reason' => new Request(['new_date' => '2026-05-03', 'reason' => "   trim me   "]),
    'missing date'      => new Request([]),
    'bad date format'   => new Request(['new_date' => '01/05/2026']),
    'reason too long'   => new Request(['new_date' => '2026-05-04', 'reason' => str_repeat('x', 501)]),
];

foreach ($cases as $label => $request) {
    DB::reset();
    $response = $controller($request, 17);
    printf("%-18s -> %d %s\n", $label, $response->status, json_encode($response->data));
    foreach (DB::$updates as $u) {
        printf("    db update id=%d values=%s\n", $u['id'], json_encode($u['values']));
    }
}
