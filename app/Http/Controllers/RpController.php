<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenIDConnect\Client;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use OpenIDConnect\Token\TokenSet;
use Ory\Hydra\Client\Api\PublicApi;
use RuntimeException;

/**
 * @see http://web.localhost:8080/rp/login
 */
class RpController
{
    public function login(Request $request, Client $oidc)
    {
        $parameters = array_merge([
            'response_type' => 'code',
            'scope' => 'openid offline_access',
            'redirect_uri' => env('HYDRA_REDIRECT_URI'),
        ], $request->all());

        $authorizationUrl = $oidc->createAuthorizeRedirectResponse($parameters);

        $request->session()->put('state', $oidc->getState());

        return $authorizationUrl;
    }

    public function refreshToken(Request $request, PublicApi $hydra)
    {
        $refreshToken = $request->get('refresh_token');

        $tokenResponse = $hydra->oauth2Token('refresh_token', null, $refreshToken);

        dump(json_decode((string)$tokenResponse, true));
    }

    public function callback(Request $request, Client $oidc)
    {
        $session = $request->session();

        if ($request->has('error')) {
            dd($request->all());
        }

        try {
            /** @var TokenSet $tokenSet */
            $tokenSet = $oidc->handleCallback($request->all(), [
                'state' => $session->get('state'),
                'redirect_uri' => env('HYDRA_REDIRECT_URI'),
            ]);

            Log::info('Token Response', $tokenSet->toArray());
        } catch (OpenIDProviderException $e) {
            Log::error('Token endpoint return some error when perform Hydra');

            throw new RuntimeException('Hydra return error', 0, $e);
        }

        dump($tokenSet->jsonSerialize());

        $idToken = $tokenSet->idTokenClaims();

        dump($idToken->all());

        dump($tokenSet->idToken());

        $session->flush();
    }
}
