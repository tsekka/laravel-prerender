<?php

namespace Tsekka\Prerender;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Cache\Repository as CacheRepository;
use Tsekka\Prerender\Actions\RecordOrTouchPrerenderedPage;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Prerender
{
    public CacheRepository|null $cache;

    public int|null $cacheTtl;

    public function __construct()
    {
        $this->cache = config('prerender.cache_driver')
            ? Cache::store(config('prerender.cache_driver'))
            : null;
        $this->cacheTtl = config('prerender.cache');
    }

    public function cacheKey($url)
    {
        return 'prerender-' . $url;
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

        $cacheKey = $this->cacheKey($url);
        (new RecordOrTouchPrerenderedPage($url, $cacheKey))
            ->handle();
        return $this->cache->put($cacheKey, $response, $this->cacheTtl);
    }

    public function getCachedResponse(string $url): SymfonyResponse|null
    {
        if (
            $this->cacheEnabled()
            && $cached = $this->cache->get($this->cacheKey($url))
        )
            return $cached;

        return null;
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
