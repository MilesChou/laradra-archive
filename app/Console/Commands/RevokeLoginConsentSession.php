<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Ory\Hydra\Client\Api\AdminApi;

/**
 * Revoke a Subject's Login and Consent Session
 *
 * @see RevokeLoginSession
 * @see RevokeConsentSession
 */
class RevokeLoginConsentSession extends Command
{
    protected $signature = 'hydra:login-consent:session:revoke
                            {subject* : sub}';

    protected $description = "Revoke login and consent Subject's Authentication Session";

    public function handle(AdminApi $admin): int
    {
        $config = $admin->getConfig();
        $host = $config->getHost();

        if ($this->output->isVeryVerbose()) {
            $this->output->note('Host: ' . $host);
        }

        $subject = $this->argument('subject');

        $requests = collect($subject)->reduce(function ($arr, $subject) use ($host) {
            $arr[] = $this->createRevokeConsentSessionRequest($host, $subject);
            $arr[] = $this->createRevokeLoginSessionRequest($host, $subject);

            return $arr;
        }, []);

        shuffle($requests);

        $client = new Client([
            RequestOptions::TIMEOUT => 300,
        ]);

        $pool = new Pool($client, $requests, [
            'concurrency' => 5,
            'fulfilled' => function (Response $response, $index) {
                $this->line('Success: ' . $index);
            },
            'rejected' => function (RequestException $reason) {
                $this->line('Oh, ' . $reason);
            },
        ]);

        $pool->promise()->wait();

        $this->output->success('Revoke Session successfully!');

        return 0;
    }

    private function createRevokeLoginSessionRequest(string $host, string $subject): Request
    {
        return new Request('DELETE', $host . "/oauth2/auth/sessions/login?subject={$subject}");
    }

    private function createRevokeConsentSessionRequest(string $host, string $subject): Request
    {
        return new Request('DELETE', $host . "/oauth2/auth/sessions/consent?subject={$subject}");
    }
}
