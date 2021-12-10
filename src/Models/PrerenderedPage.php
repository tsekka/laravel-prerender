<?php

namespace Tsekka\Prerender\Models;

use Illuminate\Database\Eloquent\Model;
use Tsekka\Prerender\Models\CrawlerVisit;

class PrerenderedPage extends Model
{
    protected $fillable = [
        'url',
        'cache_key',
    ];

    protected $casts = [
        'id' => 'integer',
        'pruned_visits' => 'integer',
    ];

    public function crawlerVisits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(CrawlerVisit::class);
    }

}
