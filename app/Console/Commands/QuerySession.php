<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Laradra\Models\HydraOauth2AuthenticationSession;

class QuerySession extends Command
{
    protected $signature = 'hydra:session:query
                            {--subject= : sub claim in ID token}
                            {--ago=2592000 : Filter some seconds ago by authenticated_at}
                            {--asc : Order by authenticated_at ASC}
                            {--desc : Order by authenticated_at DESC}
                            {--limit=100}
                            {--delete : Run delete}
                                ';

    protected $description = 'Query session (by database connection)';

    public function handle(): int
    {
        $query = HydraOauth2AuthenticationSession::query();

        $subject = $this->option('subject');
        $ago = $this->option('ago');

        $dateTimeString = Carbon::now()->subSeconds($ago)->utc()->toDateTimeString();

        Log::debug('Ago option trans to DateTime string: ' . $dateTimeString);

        $query->where('authenticated_at', '<', $dateTimeString);

        if ($subject) {
            $query->where('subject', '=', $subject);
        }

        if ($this->option('asc')) {
            $query->orderBy('authenticated_at');
        }

        if ($this->option('desc')) {
            $query->orderByDesc('authenticated_at');
        }

        if ($limit = (int)$this->option('limit')) {
            $query->limit($limit);
        }

        if (config('app.debug')) {
            $explain = $query->explain()
                ->map(function ($value) {
                    return (array)$value;
                });

            Log::debug('Query session SQL: ' . $query->toSql(), $explain->toArray());
        }

        $startTime = microtime(true);

        if ($this->option('delete')) {
            Log::debug('Query session to Delete');

            $rows = $query->delete();

            Log::debug(sprintf('Time use %s ms', (microtime(true) - $startTime) * 1000));
        } else {
            $query->chunk(10, function (Collection $collection) {
                $collection->each(function (HydraOauth2AuthenticationSession $item) {
                    $this->info('Auth time: ' . $item->authenticated_at);
                });
            });
        }

        Log::debug(sprintf('Time use %s ms', (microtime(true) - $startTime) * 1000));

        return 0;
    }
}
