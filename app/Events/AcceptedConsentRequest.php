<?php

namespace App\Events;

use Ory\Hydra\Client\Model\AcceptConsentRequest;
use Ory\Hydra\Client\Model\ConsentRequest;

class AcceptedConsentRequest
{
    /**
     * @var ConsentRequest
     */
    public $consentRequest;

    /**
     * @var AcceptConsentRequest
     */
    public $acceptConsentRequest;

    public function __construct(ConsentRequest $consentRequest, AcceptConsentRequest $acceptConsentRequest)
    {
        $this->consentRequest = $consentRequest;
        $this->acceptConsentRequest = $acceptConsentRequest;
    }
}
