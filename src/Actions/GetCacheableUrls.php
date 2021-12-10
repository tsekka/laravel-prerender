<?php

namespace Tsekka\Prerender\Actions;

use Tsekka\Prerender\Models\PrerenderedPage;

class GetCacheableUrls
{
    public function handle(): array
    {
        return PrerenderedPage::all()->pluck('url')->toArray();
    }
}
