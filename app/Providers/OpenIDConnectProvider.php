<?php

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use OpenIDConnect\Client;
use OpenIDConnect\Config;
use OpenIDConnect\Jwt\JwkSet;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;
use Ory\Hydra\Client\Api\PublicApi;

class OpenIDConnectProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            Client::class,
        ];
    }

    public function register()
    {
        $this->app->singleton(Client::class, function () {
            /** @var PublicApi $hydra */
            $hydra = $this->app->make(PublicApi::class);

            $providerConfig = json_decode((string)$hydra->discoverOpenIDConfiguration(), true);
            $jwksConfig = json_decode((string)$hydra->wellKnown(), true);

            $provider = new ProviderMetadata($providerConfig, new JwkSet($jwksConfig));

            $config = new Config(
                $provider,
                new ClientMetadata([
                    'client_id' => config('openid_connect.client.id'),
                    'client_secret' => config('openid_connect.client.secret'),
                ])
            );

            return new Client($config, $this->app->make(HttpClientInterface::class));
        });
    }
}
