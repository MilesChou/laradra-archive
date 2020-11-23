<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Api\PublicApi;
use Ory\Hydra\Client\Model\PreviousConsentSession;

/**
 * Get consent session
 */
class ConsentSession extends Command
{
    protected $signature = 'hydra:consent:session
                            {--count : only show count}
                            {subject : sub claim in ID token}';

    protected $description = 'Simulate login on Hydra system';

    public function handle(AdminApi $admin): int
    {
        if ($this->output->isVeryVerbose()) {
            $config = $admin->getConfig();

            $this->output->note('Host: ' . $config->getHost());
        }

        $subject = (string)$this->argument('subject');

        $previousConsentSessions = $admin->listSubjectConsentSessions($subject);

        $count = count($previousConsentSessions);

        $this->output->success("Subject '{$subject}' has {$count} consent sessions");

        if ($this->option('count')) {
            return 0;
        }

        $table = collect($previousConsentSessions)->map(static function (PreviousConsentSession $session) {
            return [
                'clientId' => $session->getConsentRequest()->getClient()->getClientId(),
                'scope' => implode(',', $session->getGrantScope()),
                'handledAt' => $session->getHandledAt()->format('Y-m-d H:i:s'),
            ];
        });

        $this->table(['clientId', 'scope', 'handledAt'], $table->toArray());

        return 0;
    }
}
