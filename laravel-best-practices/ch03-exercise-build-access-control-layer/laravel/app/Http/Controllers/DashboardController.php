<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class DashboardController
{
    public function __invoke(): Response
    {
        return response('Dashboard', 200);
    }
}
