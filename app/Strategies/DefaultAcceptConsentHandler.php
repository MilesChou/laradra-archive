<?php

namespace App\Strategies;

use App\Contracts\Strategies\AcceptConsentHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ory\Hydra\Client\Model\AcceptConsentRequest;
use Ory\Hydra\Client\Model\ConsentRequest;

/**
 * Auto accept all scope on every client, just accept
 */
class DefaultAcceptConsentHandler implements AcceptConsentHandler
{
    public function handleConsentRequest(Request $request, ConsentRequest $consentRequest): AcceptConsentRequest
    {
        $requestScopes = explode(' ', $consentRequest->getClient()->getScope());

        return new AcceptConsentRequest([
            'grantScope' => $requestScopes,
        ]);
    }
}
