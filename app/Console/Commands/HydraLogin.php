<?php

namespace App\Console\Commands;

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
                            {subject : sub claim in ID token}';

    protected $description = 'Simulate login on Hydra system';

    public function handle(PublicApi $public, AdminApi $admin): int
    {
        $subject = $this->argument('subject');
        $clientId = env('OPENID_CONNECT_CLIENT_ID');
        $clientSecret = env('OPENID_CONNECT_CLIENT_SECRET');
        $redirectUri = env('OPENID_CONNECT_REDIRECT_URI');

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

        $response = Http::withoutRedirecting()->get($authorizationEndpoint, $query);

        if ($this->output->isVeryVerbose()) {
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

        $response = Http::withoutRedirecting()
            ->withCookies([
                'oauth2_authentication_csrf' => $csrfToken,
            ], $authorizationDomain)
            ->get($redirectTo);

        $loginSession = $this->parseCookie($response, 'oauth2_authentication_session');
        $csrfToken = $this->parseCookie($response, 'oauth2_consent_csrf');

        if ($this->output->isVeryVerbose()) {
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

        $response = Http::withoutRedirecting()
            ->withCookies([
                'oauth2_consent_csrf' => $csrfToken,
            ], $authorizationDomain)
            ->get($redirectTo);

        $redirectQuery = $this->parseRedirectTo($response, true);

        $tokenEndpoint = $openIDConfiguration->getTokenEndpoint();

        $response = Http::withoutRedirecting()
            ->withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post($tokenEndpoint, [
                'code' => $redirectQuery['code'],
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);

        $this->output->success('Login and Consent successfully');

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
            throw new RuntimeException("No '{$key}' cookie");
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
