<?php

namespace App\Strategies;

use App\Contracts\Strategies\RejectConsentHandler;
use Illuminate\Http\Request;
use Ory\Hydra\Client\Model\ConsentRequest;
use Ory\Hydra\Client\Model\RejectRequest;

/**
 * Reject without message, just reject.
 */
class DefaultRejectConsentHandler implements RejectConsentHandler
{
    public function handleConsentRequest(Request $request, ConsentRequest $consentRequest): RejectRequest
    {
        return new RejectRequest();
    }
}
