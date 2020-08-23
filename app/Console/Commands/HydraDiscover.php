<?php

namespace App\Console\Commands;

use Hydra\SDK\Api\PublicApi;
use Illuminate\Console\Command;

class HydraDiscover extends Command
{
    protected $signature = 'hydra:discover';

    protected $description = 'Download OpenID Connect configurations';

    public function handle(PublicApi $hydra): int
    {
        return 0;
    }
}
