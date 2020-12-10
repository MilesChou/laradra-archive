<?php

namespace App\Http\Controllers\Provider\Consent;

use App\Contracts\Strategies\RejectConsentHandler;
use App\Events\RejectedConsentRequest;
use App\Exceptions\ConsentRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;

class Reject
{
    public function __invoke(Request $request, AdminApi $hydra, RejectConsentHandler $handler)
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

        $rejectRequest = $handler->handleConsentRequest($request, $consentRequest);

        try {
            $completed = $hydra->rejectConsentRequest($consentChallenge, $rejectRequest);
        } catch (ApiException $e) {
            Log::error('Hydra Admin API Error: ' . $e->getMessage(), [
                'response' => json_decode($e->getResponseBody(), true),
            ]);

            throw new ConsentRequestException("Could not handle consent_challenge '{$consentChallenge}'");
        }

        event(new RejectedConsentRequest($consentRequest, $rejectRequest));

        return redirect()->to($completed->getRedirectTo());
    }
}
