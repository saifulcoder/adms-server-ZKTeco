<?php

namespace App\Providers;

use App\Services\PushChecadaService;
use Illuminate\Support\ServiceProvider;

class WebrosterAPIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PushChecadaService::class, function () {
            // Use the parameters array to retrieve 'apiName' dynamically
            return new PushChecadaService();
        });
    }
}