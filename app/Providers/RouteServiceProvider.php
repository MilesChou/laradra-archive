<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public function map(): void
    {
        $this->mapOpenidProviderRoutes();
        $this->mapWebRoutes();
    }

    protected function mapOpenidProviderRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\\Provider')
            ->group(base_path('routes/openid_provider.php'));
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }
}
