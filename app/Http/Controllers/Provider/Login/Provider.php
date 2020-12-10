<?php

namespace App\Http\Controllers\Provider\Login;

use App\Exceptions\LoginRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;
use Ory\Hydra\Client\Model\AcceptLoginRequest;

class Provider
{
    public function __invoke(Request $request, AdminApi $admin)
    {
        $loginChallenge = $request->get('login_challenge');

        try {
            $loginRequest = $admin->getLoginRequest($loginChallenge);

            Log::debug('Get the LoginRequest', json_decode((string)$loginRequest, true));
        } catch (ApiException $e) {
            Log::error('Hydra Admin API Error: ' . $e->getMessage(), [
                'response' => json_decode($e->getResponseBody(), true),
            ]);

            throw new LoginRequestException("Could not find login_challenge '{$loginChallenge}'");
        }

        if ($loginRequest->getSkip()) {
            try {
                $completed = $admin->acceptLoginRequest($loginChallenge, new AcceptLoginRequest([
                    'subject' => $loginRequest->getSubject(),
                ]));
            } catch (ApiException $e) {
                Log::error('Hydra Admin API Error: ' . $e->getMessage(), [
                    'body' => json_decode($e->getResponseBody(), true),
                ]);

                throw new LoginRequestException("Could not accept login_challenge '{$loginChallenge}'");
            }

            return redirect()->to($completed->getRedirectTo());
        }

        return view('provider.login_page', [
            'login_challenge' => $loginChallenge,
        ]);
    }
}
