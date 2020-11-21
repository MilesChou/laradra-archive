<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Strategies\AcceptConsentHandler;
use App\Events\AcceptedConsentRequest;
use App\Exceptions\ConsentRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;

class ConsentAcceptAction
{
    public function __invoke(Request $request, AdminApi $hydra, AcceptConsentHandler $handler)
    {
        $consentChallenge = $request->input('consent_challenge');

        try {
            $consentRequest = $hydra->getConsentRequest($consentChallenge);
        } catch (ApiException $e) {
            Log::error('Hydra Admin API Error: ' . $e->getMessage(), [
                'response' => json_decode($e->getResponseBody(), true),
            ]);

            throw new ConsentRequestException("Could not find consent_challenge '{$consentChallenge}'");
        }

        $acceptConsentRequest = $handler->handleConsentRequest($request, $consentRequest);

        try {
            $completed = $hydra->acceptConsentRequest($consentChallenge, $acceptConsentRequest);
        } catch (ApiException $e) {
            Log::error('Hydra Admin API Error: ' . $e->getMessage(), [
                'response' => json_decode($e->getResponseBody(), true),
            ]);

            throw new ConsentRequestException("Could not handle consent_challenge '{$consentChallenge}'");
        }

        event(new AcceptedConsentRequest($consentRequest, $acceptConsentRequest));

        return redirect()->to($completed->getRedirectTo());
    }
}
