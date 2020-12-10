<?php

namespace App\Http\Controllers\Provider\Login;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;
use Ory\Hydra\Client\Model\AcceptLoginRequest;
use Ory\Hydra\Client\Model\RejectRequest;

class Reject
{
    public function __invoke(Request $request, AdminApi $admin)
    {
        $loginChallenge = $request->get('login_challenge');

        try {
            $completed = $admin->rejectLoginRequest($loginChallenge, new RejectRequest());
        } catch (ApiException $e) {
            Log::error('Accept login request ERROR', [
                'response' => $e->getResponseObject(),
            ]);

            throw $e;
        }

        return redirect()->to($completed->getRedirectTo());
    }
}
