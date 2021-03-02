<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ory\Hydra\Client\Api\PublicApi;

class HydraDiscover extends Command
{
    protected $signature = 'hydra:discover';

    protected $description = 'Download OpenID Connect configurations';

    public function handle(PublicApi $hydra): int
    {
        return 0;
    }
}
