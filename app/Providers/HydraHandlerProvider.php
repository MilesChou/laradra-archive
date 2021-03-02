<?php

namespace App\Providers;

use App\Contracts\Strategies\AcceptConsentHandler;
use App\Contracts\Strategies\RejectConsentHandler;
use App\Strategies\DefaultAcceptConsentHandler;
use App\Strategies\DefaultRejectConsentHandler;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class HydraHandlerProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            AcceptConsentHandler::class,
            RejectConsentHandler::class,
        ];
    }

    public function register()
    {
        $this->app->singleton(AcceptConsentHandler::class, DefaultAcceptConsentHandler::class);
        $this->app->singleton(RejectConsentHandler::class, DefaultRejectConsentHandler::class);
    }
}
