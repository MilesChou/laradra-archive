<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Api\PublicApi;
use Ory\Hydra\Client\ApiException;
use Ory\Hydra\Client\Model\AcceptLoginRequest;
use RuntimeException;

/**
 * Simulate login on Hydra system
 */
class HydraLogin extends Command
{
    protected $signature = 'hydra:login
                            {--C|context=* : Context for handle login request, e.g. "foo=bar"}
                            {--client=some-client : Set client ID}
                            {--secret=some-secret : Set client secret}
                            {--redirect-uri=http://web.localhost:8080/rp/callback : Set redirect uri}
                            {--threshold=1000 : Slow alert threshold, ms}
                            {--times=1 : Execute times}
                            {subject : sub claim in ID token}';

    protected $description = 'Simulate login on Hydra system';

    public function handle(PublicApi $public, AdminApi $admin): int
    {
        $subject = $this->argument('subject');
        $clientId = $this->option('client');
        $clientSecret = $this->option('secret');
        $redirectUri = $this->option('redirect-uri');
        $threshold = (int)$this->option('threshold') / 1000;
        $times = $this->option('times');

        foreach (range(1, $times) as $index) {
            if ($this->output->isVerbose()) {
                $this->output->section('Start');
            }

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
                $this->line('Send authorization request: ' . $authorizationEndpoint . Arr::query($query));
            }

            $startTime = microtime(true);
            $response = Http::withoutRedirecting()->get($authorizationEndpoint, $query);

            if ($this->output->isVeryVerbose()) {
                $useTime = microtime(true) - $startTime;
                if ($useTime > $threshold) {
                    $this->output->error(sprintf(
                        '[%s] Start authorization successfully, took %.3fs',
                        Carbon::now()->format('H:i:s'),
                        $useTime
                    ));
                } else {
                    $this->output->success(sprintf('Start authorization successfully, took %.3fs', $useTime));
                }

                $this->output->section('Authorization endpoint return');
                $this->line('Status code: ' . $response->status());
            }

            if (!$response->redirect()) {
                $this->error('Server return is not redirect code');
            }

            $csrfToken = $this->parseCookie($response, 'oauth2_authentication_csrf');

            if ($this->output->isVeryVerbose()) {
                $this->line('Authentication CSRF token: ' . $csrfToken);
            }

            $redirectQuery = $this->parseRedirectTo($response, true);

            $loginChallenge = $redirectQuery['login_challenge'];

            $context = $this->option('context');

            $context = collect($context)->reduce(function ($c, $v) {
                [$k, $v] = explode('=', $v);
                $c[$k] = $v;
                return $c;
            }, []);

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

                $this->output->error('Hydra admin API error when call acceptLoginRequest');

                foreach ((array)$response as $key => $value) {
                    $this->comment("{$key}:  {$value}");
                }

                return 1;
            }

            $redirectTo = $completedRequest->getRedirectTo();

            $startTime = microtime(true);
            $response = Http::withoutRedirecting()
                ->withCookies([
                    'oauth2_authentication_csrf' => $csrfToken,
                ], $authorizationDomain)
                ->get($redirectTo);

            $loginSession = $this->parseCookie($response, 'oauth2_authentication_session');
            $csrfToken = $this->parseCookie($response, 'oauth2_consent_csrf');

            if ($this->output->isVeryVerbose()) {
                $useTime = microtime(true) - $startTime;
                if ($useTime > $threshold) {
                    $this->output->error(sprintf(
                        '[%s] Authentication successfully, took %.3fs',
                        Carbon::now()->format('H:i:s'),
                        $useTime
                    ));
                } else {
                    $this->output->success(sprintf('Authorization successfully, took %.3fs', $useTime));
                }

                $this->output->section('Authorization endpoint authentication return');

                $this->line('Status code: ' . $response->status());
                $this->line('Login Session: ' . $loginSession);
                $this->line('Consent CSRF token: ' . $csrfToken);
            }

            $redirectQuery = $this->parseRedirectTo($response, true);

            $consentChallenge = $redirectQuery['consent_challenge'];

            $consentRequest = $admin->getConsentRequest($consentChallenge);

            if ($this->output->isVeryVerbose()) {
                $oidcContext = json_decode((string)$consentRequest->getOidcContext(), true);
                if (!empty($oidcContext)) {
                    $this->output->section('Oidc context');
                    $this->table(['key', 'value'], collect($oidcContext)->map(function ($v, $k) {
                        return [$k, $v];
                    })->toArray());
                }

                $this->output->section('Context');
                $context = $consentRequest->getContext();

                $this->table(['key', 'value'], collect($context)->map(function ($v, $k) {
                    if (!is_string($v)) {
                        $v = json_encode($v);
                    }

                    return [$k, $v];
                })->toArray());
            }

            try {
                $completedRequest = $admin->acceptConsentRequest($consentChallenge, [
                    'grant_scope' => ['openid'],
                ]);
            } catch (ApiException $e) {
                $response = $e->getResponseBody();

                $this->output->error('Hydra admin API error when call acceptConsentRequest');

                foreach ((array)$response as $key => $value) {
                    $this->comment("{$key}:  {$value}");
                }

                return 1;
            }

            $redirectTo = $completedRequest->getRedirectTo();

            $startTime = microtime(true);
            $response = Http::withoutRedirecting()
                ->withCookies([
                    'oauth2_consent_csrf' => $csrfToken,
                ], $authorizationDomain)
                ->get($redirectTo);

            $useTime = microtime(true) - $startTime;

            if ($useTime > $threshold) {
                $this->output->error(sprintf(
                    '[%s] Consent successfully, took %.3fs',
                    Carbon::now()->format('H:i:s'),
                    $useTime
                ));
            } else {
                $this->output->success(sprintf('Consent successfully, took %.3fs', $useTime));
            }

            $redirectQuery = $this->parseRedirectTo($response, true);

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

            $useTime = microtime(true) - $startTime;

            if ($useTime > $threshold) {
                $this->output->error(sprintf(
                    '[%s] Login and Consent successfully, token request took %.3fs',
                    Carbon::now()->format('H:i:s'),
                    $useTime
                ));
            } else {
                $this->output->success(sprintf('Login and Consent successfully, token request took %.3fs', $useTime));
            }

            $idToken = $response->json('id_token');

            $this->output->listing([
                'Access Token: ' . $response->json('access_token'),
                'ID Token: ' . $idToken,
            ]);

            [, $payload,] = Str::of($idToken)->explode('.');

            $this->output->section('ID Token claims');

            $this->table(['key', 'value'], collect(json_decode(base64_decode($payload), true))->map(function ($v, $k) {
                if (!is_string($v)) {
                    $v = json_encode($v);
                }
                return [$k, $v];
            })->toArray());
        }

        return 0;
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
            $this->output->error('Redirect ERROR');

            $this->table(['key', 'value'], collect($query)->map(function ($v, $k) {
                return [$k, $v];
            })->toArray());

            throw new RuntimeException('error');
        }

        if ($this->output->isVerbose()) {
            $this->output->section('Redirect to ->');
            $this->line($redirectTo);
        }

        if ($this->output->isVeryVerbose()) {
            $this->output->section('Query table');
            $this->table(['key', 'value'], collect($query)->map(function ($v, $k) {
                return [$k, $v];
            })->toArray());
        }

        return $query;
    }
}
