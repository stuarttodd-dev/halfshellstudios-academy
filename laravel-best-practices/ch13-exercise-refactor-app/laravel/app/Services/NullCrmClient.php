<?php

namespace App\Services;

use App\Contracts\CrmClient;
use App\Models\Lead;

class NullCrmClient implements CrmClient
{
    public function submitLead(Lead $lead): void
    {
        // No HTTP call — fine for local exercise apps; swap for HttpCrmClient with a real base URL in staging.
    }
}
