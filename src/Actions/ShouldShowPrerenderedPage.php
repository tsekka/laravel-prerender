<?php

namespace Tsekka\Prerender\Actions;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ShouldShowPrerenderedPage
{

    /**
     * List of crawler user agents that will be
     *
     * @var array
     */
    private $crawlerUserAgents;

    /**
     * Predefined string that request's user agent contained.
     *
     * @var string|null
     */
    public $matchingUserAgent = null;


    /**
     * URI whitelist for prerendering pages only on this list
     *
     * @var array
     */
    private $whitelist;

    /**
     * URI blacklist for prerendering pages that are not on the list
     *
     * @var array
     */
    private $blacklist;

    /**
     * Is prerender service enabled
     *
     * @var bool
     */
    private $enabled;

    public function __construct(
        private Request $request
    ) {
        $this->crawlerUserAgents = config('prerender.crawler_user_agents');
        $this->whitelist = config('prerender.whitelist');
        $this->blacklist = config('prerender.blacklist');
        $this->enabled = config('prerender.enable');
    }

    public function handle()
    {
        if (!$this->enabled) return false;
        $userAgent = strtolower($this->request->server->get('HTTP_USER_AGENT'));

        $bufferAgent = $this->request->server->get('X-BUFFERBOT');
        $requestUri = $this->request->getRequestUri();
        $referer = $this->request->headers->get('Referer');

        $isRequestingPrerenderedPage = false;

        if (!$userAgent) return false;
        if (!$this->request->isMethod('GET')) return false;

        // prerender if _escaped_fragment_ is in the query string
        if ($this->request->query->has('_escaped_fragment_')) $isRequestingPrerenderedPage = true;

        // prerender if a crawler is detected
        foreach ($this->crawlerUserAgents as $crawlerUserAgent) {
            if (Str::contains($userAgent, strtolower($crawlerUserAgent))) {
                $this->matchingUserAgent = $crawlerUserAgent;
                $isRequestingPrerenderedPage = true;
            }
        }

        if ($bufferAgent) $isRequestingPrerenderedPage = true;

        if (!$isRequestingPrerenderedPage) return false;

        // only check whitelist if it is not empty
        if ($this->whitelist) {
            if (!$this->isListed($requestUri, $this->whitelist)) {
                return false;
            }
        }

        // only check blacklist if it is not empty
        if ($this->blacklist) {
            $uris[] = $requestUri;
            // we also check for a blacklisted referer
            if ($referer) $uris[] = $referer;
            if ($this->isListed($uris, $this->blacklist)) {
                return false;
            }
        }

        // Okay! Prerender please.
        return true;
    }

    /**
     * Check whether one or more needles are in the given list
     *
     * @param array|string $needles
     * @param array $list
     * @return bool
     */
    private function isListed(array|string $needles, array $list): bool
    {
        $needles = is_array($needles) ? $needles : [$needles];

        foreach ($list as $pattern) {
            foreach ($needles as $needle) {
                if (Str::is($pattern, $needle)) {
                    return true;
                }
            }
        }
        return false;
    }
}
