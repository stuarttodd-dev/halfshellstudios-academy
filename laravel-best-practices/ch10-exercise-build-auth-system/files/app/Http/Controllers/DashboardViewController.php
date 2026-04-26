<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardViewController
{
    public function show(): View
    {
        return view('dashboard');
    }
}
