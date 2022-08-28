<?php

namespace Tsekka\Prerender;

use Illuminate\Support\Facades\Cache;
use Tsekka\Prerender\Models\PrerenderedPage;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Cache\Repository as CacheRepository;
use Tsekka\Prerender\Actions\RecordOrTouchPrerenderedPage;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Prerender
{
    public CacheRepository|null $cache;

    public int|null $cacheTtl;

    public bool|null $matchesCrawler = null;

    public string|null $matchingUserAgent = null;

    public function __construct()
    {
        $this->cache = config('prerender.cache_driver')
            ? Cache::store(config('prerender.cache_driver'))
            : null;
        $this->cacheTtl = config('prerender.cache');
    }

    public function cacheKey(PrerenderedPage $prerenderedPage): string
    {
        return 'prerender-' . $prerenderedPage->id;
    }

    public function cache(): ?CacheRepository
    {
        return $this->cache;
    }

    public function cacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    public function cacheEnabled()
    {
        if (!$this->cache || !$this->cacheTtl) return false;
        return true;
    }

    public function cacheTheResponse(string $url, SymfonyResponse $response): bool
    {
        if (!$this->cacheEnabled()) return false;

        $prerenderedPage = (new RecordOrTouchPrerenderedPage($url))
            ->handle();

        $cacheKey = $this->cacheKey($prerenderedPage);

        return $this->cache->put($cacheKey, $response, $this->cacheTtl);
    }

    public function getCachedResponse(string $url): SymfonyResponse|null
    {
        if (!$this->cacheEnabled()) return null;

        $prerenderedPage = PrerenderedPage::where('url', $url)->first();
        if (!$prerenderedPage) return null;

        $cached = $this->cache->get($this->cacheKey($prerenderedPage));
        return $cached ?? null;
    }

    /**
     * Convert a Guzzle Response to a Symfony Response
     *
     * @param GuzzleResponse $prerenderedResponse
     * @return SymfonyResponse
     */
    public function buildSymfonyResponseFromGuzzleResponse(GuzzleResponse $prerenderedResponse): SymfonyResponse
    {
        return (new HttpFoundationFactory)->createResponse($prerenderedResponse);
    }
}
