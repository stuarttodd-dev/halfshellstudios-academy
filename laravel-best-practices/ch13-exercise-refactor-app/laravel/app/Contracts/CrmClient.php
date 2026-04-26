<?php

namespace App\Contracts;

use App\Models\Lead;

interface CrmClient
{
    public function submitLead(Lead $lead): void;
}
