<?php

namespace App\Providers;

use Hydra\SDK\Api\PublicApi;
use Hydra\SDK\ApiClient;
use Hydra\SDK\Configuration;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Configuration::class, function () {
            $config = new Configuration();
            $config->setHost('localhost');

            return new Configuration();
        });

        $this->app->singleton(ApiClient::class, function () {
            return new ApiClient($this->app->make(Configuration::class));
        });

        $this->app->singleton(PublicApi::class, function () {
            return new PublicApi($this->app->make(ApiClient::class));
        });
    }
}
