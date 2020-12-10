<?php

namespace App\Http\Controllers\Provider\Consent;

use App\Exceptions\ConsentRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;
use Ory\Hydra\Client\Model\AcceptConsentRequest;

class Provider
{
    public function __invoke(Request $request, AdminApi $hydra)
    {
        $consentChallenge = $request->get('consent_challenge');

        try {
            $consentRequest = $hydra->getConsentRequest($consentChallenge);

            Log::debug('Get the ConsentRequest', json_decode((string)$consentRequest, true));
        } catch (ApiException $e) {
            Log::error('Hydra Admin API error: ' . $e->getMessage(), [
                'body' => json_decode($e->getResponseBody(), true),
            ]);

            throw new ConsentRequestException("Could not find consent_challenge '{$consentChallenge}'");
        }

        if ($consentRequest->getSkip()) {
            try {
                $completed = $hydra->acceptConsentRequest($consentChallenge, new AcceptConsentRequest());
            } catch (ApiException $e) {
                throw new ConsentRequestException("Could not accept consent_challenge '{$consentChallenge}'");
            }

            return redirect()->to($completed->getRedirectTo());
        }

        return view('provider.consent_page', [
            'consent_challenge' => $consentChallenge,
            'scopes' => $consentRequest->getRequestedScope(),
        ]);
    }
}
