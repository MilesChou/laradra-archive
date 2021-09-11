<?php

namespace App\Http\Controllers\Rp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenIDConnect\Client;
use OpenIDConnect\Exceptions\OpenIDProviderException;
use RuntimeException;

/**
 * @see http://web.localhost:8080/rp/login
 */
class Callback
{
    public function __invoke(Request $request, Client $oidc)
    {
        $session = $request->session();

        if ($request->has('error')) {
            dd($request->all());
        }

        try {
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
