<?php

namespace Tsekka\Prerender\Actions;

use Tsekka\Prerender\Models\CrawlerVisit;
use Tsekka\Prerender\Models\PrerenderedPage;
use Tsekka\Prerender\Actions\RecordOrTouchPrerenderedPage;


class RecordCrawlerVisit
{

    public function __construct(
        private ?string $url,
        private string $status,
        private ?int $http_status_code,
        private ?string $user_agent,
    ) {
    }

    public function handle()
    {
        $crawlerVisit = new CrawlerVisit();
        $crawlerVisit->status = $this->status;
        $crawlerVisit->http_status_code = $this->http_status_code;
        $crawlerVisit->user_agent = $this->user_agent ?? '';
        $crawlerVisit->prerendered_page_id = $this->prerenderedPage()->id;
        $crawlerVisit->server_response_time = (microtime(true) - LARAVEL_START) * 1000;
        $crawlerVisit->save();
    }


    private function prerenderedPage(): PrerenderedPage
    {
        return (new RecordOrTouchPrerenderedPage($this->url))
            ->handle();
    }
}
