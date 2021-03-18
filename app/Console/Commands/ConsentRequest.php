<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Laradra\Models\HydraOauth2ConsentRequest;

class ConsentRequest extends Command
{
    protected $signature = 'hydra:consent:request
                            {--subject= : sub claim in ID token}
                            {--ago=2592000 : Filter some seconds ago by requested_at}
                            {--asc : Order by authenticated_at ASC}
                            {--desc : Order by authenticated_at DESC}
                            {--limit=100}
                            {--delete : Run delete}
                                ';

    protected $description = 'Query session (by database connection)';

    public function handle(): int
    {
        $query = HydraOauth2ConsentRequest::query();

        $subject = $this->option('subject');
        $ago = $this->option('ago');

        $dateTimeString = Carbon::now()->subSeconds($ago)->utc()->toDateTimeString();

        Log::debug('Ago option trans to DateTime string: ' . $dateTimeString);

        $query->where('requested_at', '<', $dateTimeString);

        if ($subject) {
            $query->where('subject', '=', $subject);
        }

        if ($this->option('asc')) {
            $query->orderBy('requested_at');
        }

        if ($this->option('desc')) {
            $query->orderByDesc('requested_at');
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

            $query->delete();
        } else {
            $query->chunk(10, function (Collection $collection) {
                $collection->each(function (HydraOauth2ConsentRequest $item) {
                    $this->info('Request time: ' . $item->requested_at);
                });
            });
        }

        Log::debug(sprintf('Time use %s ms', (microtime(true) - $startTime) * 1000));

        return 0;
    }
}
