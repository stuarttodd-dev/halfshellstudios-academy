<?php

namespace App\Actions\Sales;

use App\Contracts\CrmClient;
use App\Data\CreateLeadData;
use App\Events\LeadSubmitted;
use App\Jobs\SendLeadNotificationJob;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class CreateLead
{
    public function __construct(private readonly CrmClient $crm) {}

    public function handle(CreateLeadData $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $lead = Lead::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'message' => $data->message,
            ]);
            $this->crm->submitLead($lead);
            SendLeadNotificationJob::dispatch($lead->id);
            event(new LeadSubmitted($lead));

            return $lead;
        });
    }
}
