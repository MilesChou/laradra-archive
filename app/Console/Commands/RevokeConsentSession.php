<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;

/**
 * Revoke a Subject's Consent Session
 *
 * @see https://www.ory.sh/hydra/docs/reference/api/#revokes-consent-sessions-of-a-subject-for-a-specific-oauth-20-client
 */
class RevokeConsentSession extends Command
{
    protected $signature = 'hydra:consent:session:revoke
                            {subject : sub}
                            {client? : client}';

    protected $description = "Invalidates a Subject's Authentication Session";

    public function handle(AdminApi $admin): int
    {
        if ($this->output->isVeryVerbose()) {
            $config = $admin->getConfig();

            $this->output->note('Host: ' . $config->getHost());
        }

        $subject = (string)$this->argument('subject');
        $client = $this->argument('client');

        try {
            $admin->revokeConsentSessions($subject, $client);
        } catch (ApiException $e) {
            $this->output->error('Hydra API error: ' . $e->getMessage());

            return 1;
        }

        $this->output->success('Revoke Consent Session successfully!');

        return 0;
    }
}
