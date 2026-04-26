<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class BillingController
{
    public function __invoke(): Response
    {
        return response('Billing', 200);
    }
}
