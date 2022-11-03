<?php

namespace Tsekka\Prerender;

use Tsekka\Prerender\Prerender;
use Illuminate\Support\ServiceProvider;
use Tsekka\Prerender\Console\Commands\PruneCommand;
use Tsekka\Prerender\Http\Middleware\PrerenderMiddleware;
use Tsekka\Prerender\Console\Commands\CachePagesCommand;

class PrerenderServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    protected $package = 'tsekka/prerender';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
        
        // Registering package commands.
        $this->commands([
            PruneCommand::class,
            CachePagesCommand::class,
        ]);

        $config = $this->app['config']->get('prerender');

        if ($config['enable'] && $config['register_globally']) {
            /** @var Illuminate\Contracts\Http\Kernel $kernel */
            $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
            $kernel->pushMiddleware(PrerenderMiddleware::class);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/prerender.php', 'prerender');

        // Register the service the package provides.
        $this->app->singleton('prerender', function ($app) {
            return new Prerender;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['prerender'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {

        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/prerender.php' => config_path('prerender.php'),
        ], 'config');
    }
}
