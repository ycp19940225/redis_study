<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class MockQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mock:queue-worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('监听消息队列 post-views-increment...');
        while (true) {
            $postId = Redis::lpop('popular_posts_queue');
            if (!empty($postId) && (new \App\Models\Post)->newQuery()->where('id', $postId)->increment('views')) {
                Redis::zincrBy('popular_posts', 1, $postId);
                $this->info("更新文章 #{$postId} 的浏览数");
            }
        }
    }
}
