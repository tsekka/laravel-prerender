<?php

declare(strict_types=1);

namespace Tsekka\Prerender\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tsekka\Prerender\PrerenderServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set(['prerender.enable' => true]);
        $this->setUpDatabase($this->app);
    }


    protected function getPackageProviders($app)
    {
        return [PrerenderServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'prerender' => Prerender::class,
        ];
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'App/temp' . ($suffix == '' ? '' : DIRECTORY_SEPARATOR . $suffix);
    }


    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        file_put_contents($this->getTempDirectory() . '/database.sqlite', null);

        $this->artisan('migrate')->run();
        // $this->artisan('db:seed')->run();
    }
}
