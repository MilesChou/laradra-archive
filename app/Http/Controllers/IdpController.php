<?php

namespace App\Http\Controllers;

use App\OpenIDConnect\Provider\Hydra;
use Hydra\SDK\Api\AdminApi;
use Hydra\SDK\Model\AcceptConsentRequest;
use Hydra\SDK\Model\AcceptLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IdpController
{
    public function loginPage(Request $request, Hydra $hydra)
    {
        /** @var AdminApi $admin */
        $admin = $hydra->driver('admin');

        $loginChallenge = $request->get('login_challenge');

        $loginRequest = $admin->getLoginRequest($loginChallenge);

        if ($loginRequest->getSkip()) {
            $completed = $admin->acceptLoginRequest($loginChallenge, new AcceptLoginRequest([
                'subject' => $loginRequest->getSubject(),
            ]));

            return redirect()->to($completed->getRedirectTo());
        }

        $json = (string)$loginRequest;

        dump(json_decode($json, true));

        return view('login', [
            'login_challenge' => $loginChallenge,
        ]);
    }

    public function login(Request $request, Hydra $hydra)
    {
        if ('foobar' !== $request->get('password')) {
            return redirect()->back();
        }

        /** @var AdminApi $admin */
        $admin = $hydra->driver('admin');

        $loginChallenge = $request->get('login_challenge');

        $completed = $admin->acceptLoginRequest($loginChallenge, new AcceptLoginRequest([
            'subject' => $request->get('email'),
            'remember' => true,
            'remember_for' => 3600,
        ]));

        return redirect()->to($completed->getRedirectTo());
    }

    public function consentPage(Request $request, Hydra $hydra)
    {
        /** @var AdminApi $admin */
        $admin = $hydra->driver('admin');

        $consentChallenge = $request->get('consent_challenge');

        $consentRequest = $admin->getConsentRequest($consentChallenge);

        Log::debug('Consent request', json_decode((string)$consentRequest, true));

        if ($consentRequest->getSkip()) {
            $requestedScope = $consentRequest->getRequestedScope();

            Log::info('Requested scopes', $requestedScope);

            $completed = $admin->acceptConsentRequest($consentChallenge, new AcceptConsentRequest([
                'grant_scope' => $requestedScope,
                'grant_access_token_audience' => $consentRequest->getRequestedAccessTokenAudience(),
            ]));

            return redirect()->to($completed->getRedirectTo());
        }

        $json = (string)$consentRequest;

        dump(json_decode($json, true));

        return view('consent', [
            'consent_challenge' => $consentChallenge,
        ]);
    }

    public function consent(Request $request, Hydra $hydra)
    {
        /** @var AdminApi $admin */
        $admin = $hydra->driver('admin');

        $consentChallenge = $request->get('consent_challenge');

        $requestScopes = explode(' ', $admin->getConsentRequest($consentChallenge)->getClient()->getScope());

        Log::info('Requested scopes', $requestScopes);

        $completed = $admin->acceptConsentRequest($consentChallenge, new AcceptConsentRequest([
            'grant_scope' => $requestScopes,
            'remember' => true,
            'remember_for' => 3600,
        ]));

        return redirect()->to($completed->getRedirectTo());
    }

    public function logout(Request $request, Hydra $hydra)
    {
    }
}
