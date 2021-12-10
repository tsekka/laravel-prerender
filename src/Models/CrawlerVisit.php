<?php

namespace Tsekka\Prerender\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Tsekka\Prerender\Models\PrerenderedPage;
use Illuminate\Database\Eloquent\MassPrunable;

class CrawlerVisit extends Model
{
    protected $fillable = [
        'status',
        'server_response_time',
        'cached',
        'user_agent',
    ];

    protected $casts = [
        'id' => 'integer',
        'server_response_time' => 'integer',
        'cached' => 'boolean',
    ];

    public function prerenderedPage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PrerenderedPage::class);
    }

    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PrerenderedPage::class);
    }
}
