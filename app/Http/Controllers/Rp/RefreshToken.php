<?php

namespace App\Http\Controllers\Rp;

use Illuminate\Http\Request;
use Ory\Hydra\Client\Api\PublicApi;

/**
 * @see http://web.localhost:8080/rp/login
 */
class RefreshToken
{
    public function __invoke(Request $request, PublicApi $hydra)
    {
        $refreshToken = $request->get('refresh_token');

        $tokenResponse = $hydra->oauth2Token('refresh_token', null, $refreshToken);

        dump(json_decode((string)$tokenResponse, true));
    }
}
