<?php

namespace Tsekka\Prerender\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Tsekka\Prerender\Facades\Prerender;
use Illuminate\Support\Facades\Redirect;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Tsekka\Prerender\Actions\RecordCrawlerVisit;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tsekka\Prerender\Actions\ShouldShowPrerenderedPage;
use Tsekka\Prerender\Actions\GetPrerenderedPageResponse;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PrerenderMiddleware
{
    /**
     * Return soft 3xx and 404 HTTP codes
     *
     * @var string
     */
    private $returnSoftHttpCodes;

    /**
     * Predefined string that request's user agent contained.
     *
     * @var string|null
     */
    private $matchingUserAgent;

    private string $fullUrl;
    private string $status;
    private ?int $httpStatusCode = null;

    /**
     * Creates a new PrerenderMiddleware instance
     *
     */
    public function __construct()
    {
        $config = config('prerender');
        $this->returnSoftHttpCodes = $config['prerender_soft_http_codes'];
        $this->prerenderUri = $config['prerender_url'];
        $this->crawlerUserAgents = $config['crawler_user_agents'];
        $this->prerenderToken = $config['prerender_token'];
        $this->whitelist = $config['whitelist'];
        $this->blacklist = $config['blacklist'];
    }

    /**
     * Handles a request and prerender if it should, otherwise call the next middleware.
     *
     * @param Request $request
     * @param Closure $next
     * @return RedirectResponse|SymfonyResponse|GuzzleResponse
     * @internal param int $type
     * @internal param bool $catch
     */
    public function handle(Request $request, Closure $next): RedirectResponse|SymfonyResponse|GuzzleResponse
    {
        if (!$this->shouldShowPrerenderedPage($request))
            return $next($request);

        $this->fullUrl = $request->fullUrl();

        if (
            $cachedResponse = Prerender::getCachedResponse($this->fullUrl)
        ) {
            $this->httpStatusCode
                = $cachedResponse->getStatusCode();
            $this->status = 'CACHE_HIT';
            $this->recordCrawlerVisit();
            return $cachedResponse;
        } else {
            $this->status = 'CACHE_MISSING';
        }

        if (config('prerender.run_local_server')) {
            $this->recordCrawlerVisit();
            return $next($request);
        }

        $prerenderedResponse = (new GetPrerenderedPageResponse($request))->handle();
        if (!$prerenderedResponse) {
            $this->status = 'PRERENDER_RESPONSE_MISSING';
            $this->recordCrawlerVisit();
            return $next($request);
        }

        $this->httpStatusCode = $prerenderedResponse->getStatusCode();

        $redirect =
            !$this->returnSoftHttpCodes
            && $this->httpStatusCode >= 300
            && $this->httpStatusCode < 400;

        if ($redirect) {
            $headers = $prerenderedResponse->getHeaders();
            $this->status = 'REDIRECT';
            $this->recordCrawlerVisit();
            return Redirect::to(array_change_key_case($headers, CASE_LOWER)["location"][0], $this->httpStatusCode);
        }

        $symfonyResponse = Prerender::buildSymfonyResponseFromGuzzleResponse($prerenderedResponse);
        Prerender::cacheTheResponse($this->fullUrl, $symfonyResponse);
        $this->status = 'PRERENDERED';
        $this->recordCrawlerVisit();
        return $symfonyResponse;
    }


    private function recordCrawlerVisit(): void
    {
        (new RecordCrawlerVisit(
            $this->fullUrl,
            $this->status, // our status
            $this->httpStatusCode, // http status code
            $this->matchingUserAgent
        ))->handle();
    }

    /**
     * Returns whether the request must be prerendered.
     *
     * @param Request $request
     * @return bool
     */
    private function shouldShowPrerenderedPage(Request $request): bool
    {
        $action = (new ShouldShowPrerenderedPage($request));
        $shouldShow = $action->handle();
        $this->matchingUserAgent = $action->matchingUserAgent;

        return $shouldShow;
    }
}
