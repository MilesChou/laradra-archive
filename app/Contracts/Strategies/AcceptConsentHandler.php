<?php

namespace App\Contracts\Strategies;

use Illuminate\Http\Request;
use Ory\Hydra\Client\Model\AcceptConsentRequest;
use Ory\Hydra\Client\Model\ConsentRequest;

interface AcceptConsentHandler
{
    public function handleConsentRequest(Request $request, ConsentRequest $consentRequest): AcceptConsentRequest;
}
