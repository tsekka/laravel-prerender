<?php
/*
  clear; vendor/bin/pest tests/Browser/PrerenderBrowserTest.php

  ./vendor/bin/dusk-updater detect --auto-update
*/

namespace Tsekka\Prerender\Tests\Browser;

use Laravel\Dusk\Browser;
use Tsekka\Prerender\Tests\BrowserTestCase;

/**
 * It seems to be difficult to get prerendering work on
 * dusk's envorinment, so at the moment we must have another
 * local server and full laravel app running on $testingAppUrl.
 *
 * Don't forget that prerendering engine must also be running!
 *
 */
class PrerenderBrowserTest extends BrowserTestCase
{
    public string $testingAppUrl = 'http://ehitussektor.local/';

    /** @SKIP */
    public function it_can_see_initial_content()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('test/prerender/initial')
                ->assertSeeIn('#content', 'This text should be gone after page is rendered');
        });
    }

    /** @test */
    public function it_can_see_prerendered_content_with_default_config()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSeeIn('#content h1', 'Rendered!');
        });
    }
}
