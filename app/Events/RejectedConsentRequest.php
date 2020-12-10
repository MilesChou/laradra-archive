<?php

namespace App\Events;

use Ory\Hydra\Client\Model\ConsentRequest;
use Ory\Hydra\Client\Model\RejectRequest;

class RejectedConsentRequest
{
    /**
     * @var ConsentRequest
     */
    public $consentRequest;

    /**
     * @var RejectRequest
     */
    public $rejectRequest;

    public function __construct(ConsentRequest $consentRequest, RejectRequest $rejectRequest)
    {
        $this->consentRequest = $consentRequest;
        $this->rejectRequest = $rejectRequest;
    }
}
