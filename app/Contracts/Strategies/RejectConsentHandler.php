<?php

namespace App\Contracts\Strategies;

use Illuminate\Http\Request;
use Ory\Hydra\Client\Model\ConsentRequest;
use Ory\Hydra\Client\Model\RejectRequest;

interface RejectConsentHandler
{
    public function handleConsentRequest(Request $request, ConsentRequest $consentRequest): RejectRequest;
}
