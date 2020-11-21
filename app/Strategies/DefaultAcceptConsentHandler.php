<?php

namespace App\Strategies;

use App\Contracts\Strategies\AcceptConsentHandler as AcceptConsentHandlerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Model\AcceptConsentRequest;
use Ory\Hydra\Client\Model\ConsentRequest;

/**
 * Auto accept all scope on every client
 */
class DefaultAcceptConsentHandler implements AcceptConsentHandlerContract
{
    public function handleConsentRequest(Request $request, ConsentRequest $consentRequest): AcceptConsentRequest
    {
        $requestScopes = explode(' ', $consentRequest->getClient()->getScope());

        Log::info('Requested scopes', $requestScopes);

        return new AcceptConsentRequest([
            'grant_scope' => $requestScopes,
            'remember' => true,
            'remember_for' => 3600,
        ]);
    }
}
