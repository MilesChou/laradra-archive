<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Api\PublicApi;
use Ory\Hydra\Client\Configuration;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ClientInterface::class, function () {
            return new Client();
        });

        $this->app->singleton(AdminApi::class, function () {
            $config = new Configuration();
            $config->setHost(config('hydra.admin_url'));

            return new AdminApi(
                $this->app->make(ClientInterface::class),
                $config
            );
        });

        $this->app->singleton(PublicApi::class, function () {
            $config = new Configuration();
            $config->setHost(config('hydra.public_url'));
            $config->setUsername(config('openid_connect.client.id'));
            $config->setPassword(config('openid_connect.client.secret'));

            return new PublicApi(
                $this->app->make(ClientInterface::class),
                $config
            );
        });
    }
}
