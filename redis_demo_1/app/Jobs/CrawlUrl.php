<?php

namespace App\Jobs;

use App\Models\CrawlSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlUrl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $crawlSource;

    public function __construct(CrawlSource $crawlSource)
    {
        $this->crawlSource = $crawlSource;
    }

    public function handle()
    {
        $this->crawlSource->status = 1;
        $this->crawlSource->save();
    }
}
