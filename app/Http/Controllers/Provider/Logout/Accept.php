<?php

namespace App\Http\Controllers\Provider\Logout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;

class Accept
{
    public function __invoke(Request $request, AdminApi $hydra)
    {
        $logoutChallenge = $request->get('logout_challenge');

        try {
            $completed = $hydra->acceptLogoutRequest($logoutChallenge);
        } catch (ApiException $e) {
            Log::error('Accept login request ERROR', [
                'response' => $e->getResponseObject(),
            ]);

            throw $e;
        }

        return redirect()->to($completed->getRedirectTo());
    }
}
