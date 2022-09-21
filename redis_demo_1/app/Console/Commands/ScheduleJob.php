<?php

namespace App\Console\Commands;

use Illuminate\Cache\RedisLock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Redis\Connections\Connection as RedisConnection;

class ScheduleJob extends Command
{
    protected $signature = 'schedule:job {process}';

    protected $description = 'Mock Schedule Jobs';
    /**
     * @var RedisLock
     */
    private $lock;

    public function __construct(RedisConnection $redis)
    {
        parent::__construct();
        // 基于 Redis 实现锁，过期时间 60s
        $this->lock = new RedisLock($redis, 'schedule_job', 60);
    }

    public function handle()
    {
        // 如果没有获取到锁，阻塞 5s，否则执行回调函数
        $this->lock->block(5, function () {
            $processNo = $this->argument('process');
            for ($i = 1; $i <= 10; $i++) {
                $log = "Running Job #{$i} In Process #{$processNo}";
                Storage::disk('local')->append('schedule_job_logs', $log);
            }
        });
    }
}
