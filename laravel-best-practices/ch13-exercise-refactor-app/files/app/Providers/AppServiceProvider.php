<?php

namespace App\Providers;

use App\Contracts\CrmClient;
use App\Services\NullCrmClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CrmClient::class, NullCrmClient::class);
    }

    public function boot(): void
    {
        //
    }
}
