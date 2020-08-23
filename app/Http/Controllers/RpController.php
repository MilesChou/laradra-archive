<?php

namespace App\Http\Controllers;

use App\OpenIDConnect\Client\Manager;
use App\OpenIDConnect\Provider\Hydra;
use Hydra\SDK\Api\AdminApi;
use Hydra\SDK\Api\PublicApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenIDConnect\Core\Client;
use OpenIDConnect\Core\Exceptions\OpenIDProviderException;
use OpenIDConnect\Core\Token\TokenSet;
use RuntimeException;

/**
 * @see http://127.0.0.1:8080/login
 */
class RpController
{
    public function login(Request $request, Manager $manager)
    {
        /** @var Client $hydra */
        $hydra = $manager->driver('hydra');

        $authorizationUrl = $hydra->createAuthorizeRedirectResponse([
            'response_type' => 'code',
            'scope' => 'openid offline_access',
            'redirect_uri' => env('HYDRA_REDIRECT_URI'),
        ]);

        $request->session()->put('state', $hydra->getState());

        return $authorizationUrl;
    }

    public function loginByIdToken(Request $request, Manager $manager)
    {
        $idToken = '';

        /** @var Client $hydra */
        $hydra = $manager->driver('hydra');

        $authorizationUrl = $hydra->createAuthorizeRedirectResponse([
            'response_type' => 'code',
            'scope' => 'openid',
            'redirect_uri' => env('HYDRA_REDIRECT_URI'),
            'id_token_hint' => $idToken,
            'prompt' => 'none',
        ]);

        $request->session()->put('state', $hydra->getState());

        return $authorizationUrl;
    }

    public function refreshToken(Request $request, Hydra $hydra)
    {
        $refreshToken = $request->get('refresh_token');

        /** @var PublicApi $public */
        $public = $hydra->driver('public');

        $tokenResponse = $public->oauth2Token('refresh_token', null, $refreshToken);

        dump(json_decode((string)$tokenResponse, true));
    }

    public function callback(Request $request, Manager $manager)
    {
        $session = $request->session();

        /** @var Client $hydra */
        $hydra = $manager->driver('hydra');

        if ($request->has('error')) {
            dd($request->all());
        }

        try {
            /** @var TokenSet $tokenSet */
            $tokenSet = $hydra->handleOpenIDConnectCallback($request->all(), [
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
