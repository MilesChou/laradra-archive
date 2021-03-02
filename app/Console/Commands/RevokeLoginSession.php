<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\ApiException;

/**
 * Invalidates a Subject's Authentication Session
 *
 * @see https://www.ory.sh/hydra/docs/reference/api/#invalidates-all-login-sessions-of-a-certain-user
 */
class RevokeLoginSession extends Command
{
    protected $signature = 'hydra:login:session:revoke
                            {subject : sub}';

    protected $description = "Invalidates a Subject's Authentication Session";

    public function handle(AdminApi $admin): int
    {
        if ($this->output->isVeryVerbose()) {
            $config = $admin->getConfig();

            $this->output->note('Host: ' . $config->getHost());
        }

        $subject = (string)$this->argument('subject');

        try {
            $admin->revokeAuthenticationSession($subject);
        } catch (ApiException $e) {
            $this->output->error('Hydra API error: ' . $e->getMessage());

            return 1;
        }

        $this->output->success('Invalidates Login Session successfully!');

        return 0;
    }
}
