<?php

namespace Tsekka\Prerender\Actions;

use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\App;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GetPrerenderedPageResponse
{

    /**
     * This token will be provided via the X-Prerender-Token header.
     *
     * @var string|null
     */
    private string|null $prerenderToken;

    /**
     * Base URI to make the prerender requests
     *
     * @var string
     */
    private string $prerenderUri;

    /**
     * Return soft 3xx and 404 HTTP codes
     *
     * @var bool
     */
    private bool $returnSoftHttpCodes;


    /**
     * In case of an exception, we only throw the GuzzleException or
     * RequestException if we are in debug mode. Otherwise, we'll
     * return null and the handle() method will just pass the
     * request to the next middleware and we do not show a
     * prerendered page. Other exceptions will be thrown.
     *
     * @var bool
     */
    public bool $throwExceptions;

    public function __construct(
        private Request $request
    ) {
        $this->prerenderUri = config('prerender.prerender_url');

        $this->prerenderToken = config('prerender.prerender_token');

        $this->returnSoftHttpCodes = config('prerender.prerender_soft_http_codes');

        $this->throwExceptions = config('prerender.throw_exceptions');
    }

    public function handle(): ?Response
    {
        $headers = [
            'User-Agent' => $this->request->server->get('HTTP_USER_AGENT'),
        ];

        if ($this->prerenderToken) {
            $headers['X-Prerender-Token'] = $this->prerenderToken;
        }

        $protocol = $this->request->isSecure() ? 'https' : 'http';

        try {
            // Return the Guzzle Response
            $host = $_SERVER['HTTP_HOST'] ?? $this->request->getHost(); // $_SERVER['HTTP_HOST'] includes port of server if other than http's/https's default port but could be empty on testing environment

            $path = $this->request->Path();
            // Fix "//" 404 error
            if ($path == "/") {
                $path = "";
            }
            $fullPath = $this->prerenderUri . '/' . urlencode($protocol . '://' . $host . '/' . $path);

            return $this->client()->get($fullPath, compact('headers'));
        } catch (GuzzleException $exception) {
            if ($this->throwExceptions) {
                throw $exception;
            }
            return null;
        } catch (RequestException $exception) {
            if (
                !$this->returnSoftHttpCodes
                && !empty($exception->getResponse())
                && $exception->getResponse()->getStatusCode() == 404
            ) {
                App::abort(404);
            }

            if ($this->throwExceptions) {
                throw $exception;
            }

            return null;
        }
    }

    public function client()
    {
        $client = new Guzzle();
        if (config('prerender.prerender_soft_http_codes')) {
            return $client;
        }

        // Workaround to avoid following redirects
        $config = $client->getConfig();
        $config['allow_redirects'] = false;
        return new Guzzle($config);
    }
}
