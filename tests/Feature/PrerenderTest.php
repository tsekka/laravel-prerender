<?php
/*
  clear; vendor/bin/pest tests/Feature/PrerenderTest.php
*/

namespace Tsekka\Invoiceable\Feature;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Tsekka\Prerender\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tsekka\Prerender\Actions\GetPrerenderedPageResponse;
use Tsekka\Prerender\Http\Middleware\PrerenderMiddleware;


class PrerenderTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_prerender_page()
    {
        // dd($_SERVER);
        $request = new Request;
        $userAgent = 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        $request->headers->set('User-Agent', $userAgent);
        $request->server->set('HTTP_USER_AGENT', $userAgent);
        $this->withHeaders([
            'User-Agent' => $userAgent,
        ])->post('/prerender/test');
        $response = $this->get('/prerender/test');
        $response->assertStatus(200);
        dd($response);
    }


    /** @SKIP */
    public function can_get_prerender_response()
    {

        // $request = Request::create('http://ehitussektor.local' . '/prerender/test', 'GET');
        // $userAgent = 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        // $request->headers->set('User-Agent', $userAgent);
        // $request->server->set('HTTP_USER_AGENT', $userAgent);

        // $service = (new GetPrerenderedPageResponse($request, new Client));
        // $service->throwException = true;
        // $response = $service->handle();
        // dd($response);
        // $response->getStatusCode();

        // dd($req->get('prerender_status'));


        // $middleware->handle($request, function ($req) {
        // dd($req);
        //     // dd($req->get('prerender_status'));
        //     // $this->assertEquals('Title is in Mixed Case', $req->title);
        // });

        // $this->assertTrue(true); // todo;
    }

    /** @skip */
    public function can_prerender_page_middleware()
    {

        // $request = new Request;
        // $userAgent = 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        // $request->headers->set('User-Agent', $userAgent);
        // $request->server->set('HTTP_USER_AGENT', $userAgent);

        // $middleware = new PrerenderMiddleware(app(), new Client);

        // $middleware->handle($request, function ($req) {
        //     // dd($req);
        //     // dd($req->get('prerender_status'));
        //     // $this->assertEquals('Title is in Mixed Case', $req->title);
        // });

        $this->assertTrue(true); // todo;
    }
}
