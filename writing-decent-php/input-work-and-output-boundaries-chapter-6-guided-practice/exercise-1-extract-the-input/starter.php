<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class RescheduleBookingController
{
    public function __invoke(Request $request, int $bookingId): JsonResponse
    {
        $newDate = $request->input('new_date');
        if (! $newDate || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
            return new JsonResponse(['error' => 'new_date must be YYYY-MM-DD'], 422);
        }

        $reason = trim($request->input('reason', ''));
        if (strlen($reason) > 500) {
            return new JsonResponse(['error' => 'reason too long'], 422);
        }

        DB::table('bookings')->where('id', $bookingId)->update([
            'starts_on'         => $newDate,
            'reschedule_reason' => $reason,
        ]);

        return new JsonResponse(['status' => 'rescheduled']);
    }
}

/* ---------- driver ---------- */

$controller = new RescheduleBookingController();

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
