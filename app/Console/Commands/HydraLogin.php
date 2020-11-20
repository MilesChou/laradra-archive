<?php

namespace App\Console\Commands;

use Hydra\SDK\Api\AdminApi;
use Hydra\SDK\Api\PublicApi;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Uri;
use OpenIDConnect\Core\Issuer;
use Psr\Http\Client\ClientInterface;

class HydraLogin extends Command
{
    protected $signature = 'hydra:login {subject}
                                {--client-id=}
                                {--client-secret=}
                                ';

    protected $description = 'Simulate login on Hydra system';

    public function handle(PublicApi $public, AdminApi $admin): int
    {
        $subject = $this->argument('subject');
        $clientId = $this->option('client-id');
        $clientSecret = $this->option('client-secret');

        $openIDConfiguration = $public->discoverOpenIDConfiguration();

        $authorizationEndpoint = $openIDConfiguration->getAuthorizationEndpoint();

        if ($this->output->isVerbose()) {
            $this->line('Authorization endpoint: ' . $authorizationEndpoint);
        }

        $authorizationRequest = (new RequestFactory())->createRequest('GET', (new Uri($authorizationEndpoint))
            ->withQuery(Arr::query([])));

        if ($this->output->isVerbose()) {
            $this->line('Send request to authorization endpoint: ' . $authorizationRequest->getUri());
        }

        $response = Http::get($authorizationRequest);

        if ($this->output->isVeryVerbose()) {
            $this->output->section('Authorization return');
            $this->line('  Status code: ' . $response->status());
        }


        return 0;
    }
}
