<?php

namespace App\Http\Controllers\Provider;

use App\Exceptions\ConsentRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;
use Ory\Hydra\Client\Model\AcceptConsentRequest;

class ConsentProvider
{
    public function __invoke(Request $request, AdminApi $hydra)
    {
        $consentChallenge = $request->get('consent_challenge');

        try {
            $consentRequest = $hydra->getConsentRequest($consentChallenge);

            Log::debug('Get the ConsentRequest', json_decode((string)$consentRequest, true));
        } catch (ApiException $e) {
            Log::error('Hydra Admin API Error: ' . $e->getMessage(), [
                'body' => json_decode($e->getResponseBody(), true),
            ]);

            throw new ConsentRequestException("Could not find consent_challenge '{$consentChallenge}'");
        }

        if ($consentRequest->getSkip()) {
            $requestedScope = $consentRequest->getRequestedScope();

            Log::info('Requested scopes', $requestedScope);

            $completed = $hydra->acceptConsentRequest($consentChallenge, new AcceptConsentRequest([
                'grant_scope' => $requestedScope,
                'grant_access_token_audience' => $consentRequest->getRequestedAccessTokenAudience(),
            ]));

            return redirect()->to($completed->getRedirectTo());
        }

        return view('consent', [
            'consent_challenge' => $consentChallenge,
        ]);
    }
}
