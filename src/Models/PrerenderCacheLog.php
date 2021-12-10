<?php

namespace Tsekka\Prerender\Models;

use Illuminate\Database\Eloquent\Model;
use Tsekka\Prerender\Models\CrawlerVisit;

class PrerenderCacheLog extends Model
{

    protected $fillable = [
        'status',
        'data',
    ];

    protected $casts = [
        'id' => 'integer',
        'data' => 'array',
    ];

    public function getDataAttribute($value)
    {
        return json_decode($value ?? '[]', true);
    }

    public function crawlerVisits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(CrawlerVisit::class);
    }
}
