<?php

namespace App\Http\Controllers\Rp;

use Illuminate\Http\Request;
use OpenIDConnect\Client;

/**
 * @see http://web.localhost:8080/rp/login
 */
class Login
{
    public function __invoke(Request $request, Client $oidc)
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
}
