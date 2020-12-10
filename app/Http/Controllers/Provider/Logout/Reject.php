<?php

namespace App\Http\Controllers\Provider\Logout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;
use Ory\Hydra\Client\Model\RejectRequest;

class Reject
{
    public function __invoke(Request $request, AdminApi $hydra)
    {
        $logoutChallenge = $request->get('logout_challenge');

        try {
            $hydra->rejectLogoutRequest($logoutChallenge, new RejectRequest());
        } catch (ApiException $e) {
            Log::error('Reject logout request error', [
                'response' => $e->getResponseObject(),
            ]);

            throw $e;
        }

        return 'Logout ok';
    }
}
