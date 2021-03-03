<?php

namespace App\Console\Commands;

use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Api\PublicApi;
use Ory\Hydra\Client\ApiException;
use Ory\Hydra\Client\Model\AcceptLoginRequest;
use RuntimeException;
use Spatie\Async\Pool;

/**
 * Simulate login on Hydra system
 */
class SimulateParallelLogin extends Command
{
    protected $signature = 'hydra:simulate:parallel-login
                            {--C|context=* : Context for handle login request, e.g. "foo=bar"}
                            {--client=some-client : Set client ID}
                            {--secret=some-secret : Set client secret}
                            {--redirect-uri=http://web.localhost:8080/rp/callback : Set redirect uri}
                            {--times=1 : Execute times}
                            {subject* : sub claim in ID token}';

    protected $description = 'Simulate login on Hydra system';

    public function handle(PublicApi $public, AdminApi $admin): int
    {
        $pool = Pool::create();

        foreach ($this->generator($public, $admin) as $task) {
            $pool[] = async($task);
        }

        await($pool);

        return 0;
    }

    private function generator(PublicApi $public, AdminApi $admin): iterable
    {
        $subjects = $this->argument('subject');
        $times = (int)$this->option('times');

        for ($i = 0; $i < $times; $i++) {
            $subject = $subjects[array_rand($subjects)];

            yield function () use ($public, $admin, $subject) {
                $clientId = $this->option('client');
                $clientSecret = $this->option('secret');
                $redirectUri = $this->option('redirect-uri');

                $this->line("--- {$subject} start login");

                $openIDConfiguration = $public->discoverOpenIDConfiguration();

                $authorizationEndpoint = $openIDConfiguration->getAuthorizationEndpoint();
                $authorizationDomain = (new Uri($authorizationEndpoint))->getHost();
                $query = [
                    'client_id' => $clientId,
                    'scope' => 'openid',
                    'state' => 'some-state',
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                ];

                if ($this->output->isVerbose()) {
                    $this->line("--- {$subject} send authorization request");
                }

                $startTime = microtime(true);
                $response = Http::withoutRedirecting()->get($authorizationEndpoint, $query);

                if ($this->output->isVerbose()) {
                    $useTime = microtime(true) - $startTime;

                    $this->line(sprintf(
                        "--- {$subject} Authorization successfully, took %.3fs",
                        $useTime
                    ));

                    $this->line("--- {$subject} Authorization status code: " . $response->status());
                }

                if (!$response->redirect()) {
                    $this->error("--- {$subject} Server return is not redirect code");
                }

                try {
                    $csrfToken = $this->parseCookie($response, 'oauth2_authentication_csrf');
                } catch (RuntimeException $e) {
                    $this->error("--- {$subject} ERROR: " . $e->getMessage());
                    return;
                }

                if ($this->output->isVeryVerbose()) {
                    $this->line("--- {$subject} Use authentication CSRF token: " . $csrfToken);
                }

                try {
                    $redirectQuery = $this->parseRedirectTo($response, true);
                } catch (RuntimeException $e) {
                    $this->error("--- {$subject} ERROR: " . $e->getMessage());
                    return;
                }

                $loginChallenge = $redirectQuery['login_challenge'];

                $context = $this->option('context');

                try {
                    $acceptLoginRequest = new AcceptLoginRequest([
                        'subject' => $subject,
                    ]);

                    if (!empty($context)) {
                        $acceptLoginRequest->setContext($context);
                    }

                    $completedRequest = $admin->acceptLoginRequest($loginChallenge, $acceptLoginRequest);
                } catch (ApiException $e) {
                    $response = $e->getResponseBody();

                    $this->error("--- {$subject} Hydra admin API error when call acceptLoginRequest");

                    foreach ((array)$response as $key => $value) {
                        $this->comment("{$key}:  {$value}");
                    }

                    return;
                }

                $redirectTo = $completedRequest->getRedirectTo();

                $startTime = microtime(true);
                $response = Http::withoutRedirecting()
                    ->withCookies([
                        'oauth2_authentication_csrf' => $csrfToken,
                    ], $authorizationDomain)
                    ->get($redirectTo);

                try {
                    $loginSession = $this->parseCookie($response, 'oauth2_authentication_session');
                    $csrfToken = $this->parseCookie($response, 'oauth2_consent_csrf');
                } catch (RuntimeException $e) {
                    $this->error("--- {$subject} ERROR: " . $e->getMessage());
                    return;
                }

                if ($this->output->isVeryVerbose()) {
                    $useTime = microtime(true) - $startTime;

                    $this->line(sprintf("--- {$subject} authorization successfully, took %.3fs", $useTime));
                    $this->line("--- {$subject} Status code: " . $response->status());
                    $this->line("--- {$subject} Login Session: " . $loginSession);
                    $this->line("--- {$subject} Consent CSRF token: " . $csrfToken);
                }
                try {
                    $redirectQuery = $this->parseRedirectTo($response, true);
                } catch (RuntimeException $e) {
                    $this->error("--- {$subject} ERROR: " . $e->getMessage());
                    return;
                }

                $consentChallenge = $redirectQuery['consent_challenge'];

                try {
                    $completedRequest = $admin->acceptConsentRequest($consentChallenge, [
                        'grant_scope' => ['openid'],
                    ]);
                } catch (ApiException $e) {
                    $this->error("--- {$subject} Hydra API error when call acceptConsentRequest");

                    return;
                }

                $redirectTo = $completedRequest->getRedirectTo();

                $startTime = microtime(true);
                $response = Http::withoutRedirecting()
                    ->withCookies([
                        'oauth2_consent_csrf' => $csrfToken,
                    ], $authorizationDomain)
                    ->get($redirectTo);

                $useTime = microtime(true) - $startTime;

                if ($this->output->isVerbose()) {
                    $this->line(sprintf("--- {$subject} Consent successfully, took %.3fs", $useTime));
                }

                try {
                    $redirectQuery = $this->parseRedirectTo($response, true);
                } catch (\RuntimeException $e) {
                    $this->output->error($e->getMessage());
                    return;
                }

                $tokenEndpoint = $openIDConfiguration->getTokenEndpoint();

                $startTime = microtime(true);
                $response = Http::withoutRedirecting()
                    ->withBasicAuth($clientId, $clientSecret)
                    ->asForm()
                    ->post($tokenEndpoint, [
                        'code' => $redirectQuery['code'],
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => $redirectUri,
                    ]);

                if ($response->json('error')) {
                    $this->error("--- {$subject} Token endpoint ERROR");

                    return;
                }

                $useTime = microtime(true) - $startTime;

                $this->line(sprintf(
                    "--- {$subject} Login and Consent successfully, token request took %.3fs",
                    $useTime
                ));

                if ($this->output->isVeryVerbose()) {
                    $this->line("--- {$subject} Access Token: " . $response->json('access_token'));
                    $this->line("--- {$subject} ID Token: " . $response->json('id_token'));
                }
            };
        }
    }

    /**
     * @param Response $response
     * @param string $key
     * @return string
     */
    private function parseCookie(Response $response, string $key): string
    {
        $cookie = $response->cookies()->getCookieByName($key);

        if (null === $cookie) {
            throw new RuntimeException("No '{$key}' cookie, Response: " . $response->body());
        }

        return $cookie->getValue();
    }

    private function parseRedirectTo(Response $response, $checkError = false)
    {
        $redirectTo = $response->header('Location');

        parse_str(parse_url($redirectTo, PHP_URL_QUERY), $query);

        if ($checkError && isset($query['error'])) {
            throw new RuntimeException('Redirect error');
        }

        return $query;
    }
}
