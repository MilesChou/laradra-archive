<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Ory\Hydra\Client\Api\PublicApi;

/**
 * Revoke Tokens
 *
 * @see https://www.ory.sh/hydra/docs/reference/api/#revoke-oauth2-tokens
 */
class RevokeToken extends Command
{
    protected $signature = 'hydra:revoke
                            {token : Access token or Refresh token }';

    protected $description = "Revoke Access token or Refresh token";

    public function handle(PublicApi $publicApi): int
    {
        $config = $publicApi->getConfig();
        if ($this->output->isVeryVerbose()) {
            $this->output->note('Host: ' . $config->getHost());
        }

        $token = (string)$this->argument('token');

        $response = Http::asForm()
            ->withBasicAuth(config('openid_connect.client.id'), config('openid_connect.client.secret'))
            ->post($config->getHost() . '/oauth2/revoke', [
                'token' => $token,
            ]);

        if (200 !== $response->status()) {
            $this->output->error('Hydra API error');
            $this->output->comment($response->json());

            return 1;
        }

        $this->output->success('Revoke token Session successfully!');

        return 0;
    }
}
