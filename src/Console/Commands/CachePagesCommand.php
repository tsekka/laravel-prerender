<?php

namespace Tsekka\Prerender\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Tsekka\Prerender\Facades\Prerender;
use Tsekka\Prerender\Models\PrerenderedPage;
use Tsekka\Prerender\Models\PrerenderCacheLog;
use Tsekka\Prerender\Actions\GetPrerenderedPageResponse;

class CachePagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prerender:cache {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache all prerendered pages';

    private array $unexpiredPrerenderedUrls = [];
    private bool $force;
    private bool $runServer;
    private $process;
    private PrerenderCacheLog $log;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        if (!Prerender::cacheEnabled()) {
            throw new \Exception('Please enable cache.');
        }

        $this->force = $this->option('force');
        $this->runServer = config('prerender.run_server_by_command');
        $this->log = new PrerenderCacheLog();
        $this->unexpiredPrerenderedUrls =
            PrerenderedPage::where(
                'updated_at',
                '>=',
                Carbon::now()->subSeconds(Prerender::cacheTtl())
            )
            ->get()
            ->pluck('url')
            ->toArray();

        $this->logStatus('STARTING');
        $this->startProcess();
        $this->logStatus('STARTED');

        foreach ($this->urls() as $fullUrl) {
            $url = str_replace(config('app.url'), '', $fullUrl);

            if ($this->shouldRestartProcess()) {
                $this->restartProcess();
            }

            $this->line("Starting to prerender `{$url}` ...");

            $result = $this->prerenderAndCache($fullUrl);

            $loggedContent = $this->log->content;
            $loggedContent[] = [$url, $result];

            $this->log->content = $loggedContent;
            $this->log->save();

            $message = $result . " `" . $url . "`";
            in_array($result, ['CACHED', 'SKIPPED'])
                ? $this->info($message)
                : $this->error($message);

            echo "\n";
        }
        $this->logStatus('COMPLETED');

        $this->endProcess();

        $this->logStatus('ENDED');

        $count = count($this->log->content ?? []);

        if ($count) {
            $message = 'Recaching completed. It took '
                . $this->log->updated_at->diffInSeconds($this->log->created_at)
                . ' seconds to handle '
                . $count
                . ' urls.';
        } else {
            $message = 'No urls to cache.';
        }

        $this->comment($message);
    }

    /**
     * Get the list of urls
     * @return array
     */
    private function urls(): array
    {
        $class = config('prerender.cacheable_urls')[0];
        $method = config('prerender.cacheable_urls')[1];
        return (new $class)->{$method}();
    }

    /**
     * Prerender and cache the url.
     * @param string $url
     * @return string
     */
    private function prerenderAndCache(string $url): string
    {
        if (!$this->shouldPrerenderAndCache($url)) {
            return "SKIPPED";
        }

        $request = Request::create($url, 'GET');
        $prerenderedResponse = (new GetPrerenderedPageResponse($request))->handle();

        if (!$prerenderedResponse) {
            return 'PRERENDERED_RESPONSE_MISSING';
        }

        $httpStatusCode = $prerenderedResponse->getStatusCode();

        $redirect =
            !config('prerender_soft_http_codes')
            && $httpStatusCode >= 300
            && $httpStatusCode < 400;

        if ($redirect) {
            return 'SKIPPED_REDIRECT_STATUS_CODE:' . $httpStatusCode;
        }

        $cached = Prerender::cacheTheResponse($url, $prerenderedResponse);
        return $cached ? 'CACHED' : 'NOT_CACHED';
    }
    /**
     * Start the process of node server
     *
     * @return void
     */
    private function startProcess(): void
    {
        if (!$this->runServer) return;

        $this->process = new Process(
            ['node',  'server.js'],
            __DIR__ . '/../../../prerenderer/',
            [ // ENV variables for server.js
                'PORT' => config('prerender.prerenderer_service.port', 3000),
            ]
        );

        $this->process->setTimeout(60);
        $this->process->setIdleTimeout(30);
        $this->process->start();

        foreach ($this->process as $data) {
            $this->info($data);

            if (!str_contains($data, 'Started Chrome'))
                continue;

            $this->info('Prerenderer service started.');
            return;
        }
    }

    /**
     * Check if the server process should be restarted
     *
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\InvalidCastException
     */
    private function shouldRestartProcess(): bool
    {
        if (!$this->runServer) return false;

        if ($this->process->isRunning()) return false;
        $this->warn('Prerenderer service process ended. Restarting...');
        $this->logStatus('RESTARTING_PROCESS');
        return true;
    }

    /**
     * Restart the server process
     *
     * @return void
     */
    private function restartProcess(): void
    {
        if (!$this->runServer) return;

        if ($this->process->isRunning()) return;

        try {
            $this->warn('Restarting process...');
            $this->startProcess();
            $this->warn('Process restarted.');
            $this->logStatus('PROCESS_RESTARTED');
        } catch (\Throwable $th) {
            throw $th;
        }

        $this->warn('Prerenderer service process ended. Restarting...');
    }


    /**
     * End the node server process
     *
     * @return void
     */
    private function endProcess(): void
    {
        if (!$this->runServer) return;

        $this->process->stop(3, SIGINT);
        $this->info('Prerenderer service stopped.');
    }

    /**
     * Check if specific url should be prerendered and cached
     *
     * @param string $url
     * @return bool
     */
    private function shouldPrerenderAndCache(string $url): bool
    {
        if ($this->force || !in_array($url, $this->unexpiredPrerenderedUrls))
            return true;

        return false;
    }

    /**
     * Log the status of the cycle.
     *
     * @param string $status
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\InvalidCastException
     */
    private function logStatus(string $status): void
    {
        $this->log->status = $status;
        $this->log->save();
    }
}
