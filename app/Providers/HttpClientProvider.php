<?php

namespace App\Providers;

use Http\Adapter\Guzzle6\Client as Psr18Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use MilesChou\Psr\Http\Client\HttpClientManager;
use Psr\Http\Client\ClientInterface;

class HttpClientProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            HttpClientInterface::class,
            ClientInterface::class,
        ];
    }


    public function register()
    {
        $this->app->singleton(HttpClientInterface::class, function () {
            return new HttpClientManager($this->app->make(ClientInterface::class));
        });

        $this->app->singleton(ClientInterface::class, function () {
            return new Psr18Client();
        });
    }
}
