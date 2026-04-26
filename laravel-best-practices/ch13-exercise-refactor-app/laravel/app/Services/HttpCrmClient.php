<?php

namespace App\Services;

use App\Contracts\CrmClient;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;

class HttpCrmClient implements CrmClient
{
    public function submitLead(Lead $lead): void
    {
        Http::baseUrl((string) config('services.crm.base_url'))
            ->asJson()
            ->post('/leads', [
                'email' => $lead->email,
                'name' => $lead->name,
            ])
            ->throw();
    }
}
