<?php

namespace App\Console\Commands;

use App\Jobs\CrawlUrl;
use App\Models\CrawlSource;
use Averias\RedisBloom\Enum\Connection;
use Averias\RedisBloom\Factory\RedisBloomFactory;
use Illuminate\Console\Command;

class StartCrawlUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \Averias\RedisBloom\Client\RedisBloomClientInterface
     */
    private $redisClient;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $factory = new RedisBloomFactory([
            Connection::HOST => config('database.redis.default.host'),
            Connection::PORT => intval(config('database.redis.default.port')),
            Connection::DATABASE => intval(config('database.redis.default.database'))
        ]);
        $this->redisClient = $factory->createClient();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $key = config('app.name') . '.bf.crawls';
        CrawlSource::chunk(100, function ($sources) use ($key){
            foreach ($sources as $source){
                if($this->redisClient->bloomFilterExists($key, $source->url)){
                    $this->info('已经处理了' . $source->url);
                }else{
                    CrawlUrl::dispatch($source)->onQueue('crawler');
                    $this->redisClient->bloomFilterAdd($key, $source->url);
                }
            }
        });
    }
}
