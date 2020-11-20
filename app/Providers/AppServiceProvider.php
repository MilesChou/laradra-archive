<?php

namespace App\Providers;

use Hydra\SDK\Api\AdminApi;
use Hydra\SDK\Api\PublicApi;
use Hydra\SDK\ApiClient;
use Hydra\SDK\Configuration;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AdminApi::class, function () {
            $config = new Configuration();
            $config->setHost(config('hydra.admin_url'));

            return new AdminApi(new ApiClient($config));
        });

        $this->app->singleton(PublicApi::class, function () {
            $config = new Configuration();
            $config->setHost(config('hydra.public_url'));

            return new PublicApi(new ApiClient($config));
        });
    }
}
