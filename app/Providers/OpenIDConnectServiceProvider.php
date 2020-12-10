<?php

namespace App\Providers;

use App\Contracts\Strategies\AcceptConsentHandler;
use App\Contracts\Strategies\RejectConsentHandler;
use App\Strategies\DefaultAcceptConsentHandler;
use App\Strategies\DefaultRejectConsentHandler;
use Http\Adapter\Guzzle6\Client as Psr18Client;
use Illuminate\Support\ServiceProvider;
use MilesChou\Psr\Http\Client\HttpClientInterface;
use MilesChou\Psr\Http\Client\HttpClientManager;
use OpenIDConnect\Client;
use OpenIDConnect\Config;
use OpenIDConnect\Contracts\ClientMetadataInterface;
use OpenIDConnect\Contracts\ConfigInterface;
use OpenIDConnect\Contracts\ProviderMetadataInterface;
use OpenIDConnect\Jwt\JwkSet;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;
use Ory\Hydra\Client\Api\PublicApi;
use Psr\Http\Client\ClientInterface;

class OpenIDConnectServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function () {
            return new Client(
                $this->app->make(ConfigInterface::class),
                $this->app->make(HttpClientInterface::class)
            );
        });

        $this->app->singleton(ConfigInterface::class, function () {
            return new Config(
                $this->app->make(ProviderMetadataInterface::class),
                $this->app->make(ClientMetadataInterface::class)
            );
        });

        $this->app->singleton(ProviderMetadataInterface::class, function () {
            /** @var PublicApi $hydra */
            $hydra = $this->app->make(PublicApi::class);

            $providerConfig = json_decode((string)$hydra->discoverOpenIDConfiguration(), true);
            $jwksConfig = json_decode((string)$hydra->wellKnown(), true);

            return new ProviderMetadata($providerConfig, new JwkSet($jwksConfig));
        });

        $this->app->singleton(ClientMetadataInterface::class, function () {
            return new ClientMetadata([
                'client_id' => config('openid_connect.client.id'),
                'client_secret' => config('openid_connect.client.secret'),
                'redirect_uri' => config('openid_connect.client.redirect_uri'),
            ]);
        });

        $this->app->singleton(HttpClientInterface::class, function () {
            return new HttpClientManager($this->app->make(ClientInterface::class));
        });

        $this->app->singleton(HttpClientInterface::class, function () {
            return new HttpClientManager($this->app->make(ClientInterface::class));
        });

        $this->app->singleton(ClientInterface::class, function () {
            return new Psr18Client();
        });

        $this->app->singleton(AcceptConsentHandler::class, DefaultAcceptConsentHandler::class);
        $this->app->singleton(RejectConsentHandler::class, DefaultRejectConsentHandler::class);
    }
}
