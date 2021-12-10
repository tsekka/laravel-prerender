<?php

namespace Tsekka\Prerender\Actions;

use Tsekka\Prerender\Models\PrerenderedPage;
use Tsekka\Prerender\Models\CrawlerVisit;


class RecordOrTouchPrerenderedPage
{
    public function __construct(
        private ?string $url,
        private string $cacheKey,
    ) {
    }

    public function handle(): PrerenderedPage
    {
        $prerenderedPage = PrerenderedPage::firstOrNew(
            ['url' => $this->url]
        );

        if ($this->cacheKey !== $prerenderedPage->cache_key) {
            $prerenderedPage->cache_key = $this->cacheKey;
        }

        if ($prerenderedPage->exists()) {
            $prerenderedPage->touch();
        } else {
            $prerenderedPage->save();
        }

        return $prerenderedPage;
    }
}
