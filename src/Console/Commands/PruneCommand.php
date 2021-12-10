<?php

namespace Tsekka\Prerender\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Tsekka\Prerender\Models\CrawlerVisit;
use Tsekka\Prerender\Models\PrerenderCacheLog;

class PruneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prerender:prune {time=month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune crawler visits and/or cache logs';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $time = $this->argument('time');

        if (in_array($time, ['year', 'month', 'day', 'minute', 'second'])) {
            $time = '1 ' . $time;
        }

        $pruneTo = Carbon::now();

        if ($time && $time !== 'all') {
            $pruneTo = $pruneTo->sub($time);
        }

        $visitsCount = CrawlerVisit::where('created_at', '<=', $pruneTo)->delete();

        $this->info(
            $visitsCount
                ? $visitsCount . ' crawler visits pruned until ' . $pruneTo->format('Y-m-d H:i:s')
                : 'No crawler visits to prune'
        );

        $logsCount = PrerenderCacheLog::where('created_at', '<=', $pruneTo)->delete();

        $this->info(
            $logsCount
                ? $logsCount . ' cache logs pruned until ' . $pruneTo->format('Y-m-d H:i:s')
                : 'No cache logs to prune'
        );
    }
}
