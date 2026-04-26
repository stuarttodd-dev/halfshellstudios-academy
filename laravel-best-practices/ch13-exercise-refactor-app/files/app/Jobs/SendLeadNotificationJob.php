<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLeadNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $leadId) {}

    public function handle(): void
    {
        $lead = Lead::query()->find($this->leadId);
        if ($lead === null) {
            return;
        }
        Log::info('lead mail queued (replace with Mail::to...)', ['lead_id' => $lead->id]);
    }
}
