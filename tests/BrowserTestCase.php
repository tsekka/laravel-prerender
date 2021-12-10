<?php

namespace Tsekka\Prerender\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Orchestra\Testbench\Dusk\TestCase as DuskTestCase;
use Orchestra\Testbench\Dusk\Options as DuskOptions;

class BrowserTestCase extends DuskTestCase
{

    protected static $baseServeHost = '127.0.0.1';
    protected static $baseServePort = 8001;
    protected function getPackageProviders($app)
    {
        return [\Tsekka\Prerender\PrerenderServiceProvider::class];
    }

    protected function driver(): RemoteWebDriver
    {

        $userAgent = 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--force-device-scale-factor=0.8',
            '--window-size=1920,1080',
        ]);
        $options->setExperimentalOption('mobileEmulation', ['userAgent' => $userAgent]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            $options->toCapabilities()
        );
    }
}
