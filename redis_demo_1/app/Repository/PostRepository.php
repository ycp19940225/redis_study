<?php
namespace App\Repository;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class PostRepository
{
    protected $post;
    private $trendingPostsKey = 'trending_posts_key';

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
    public function getById(int $id, array $columns = ['*'])
    {
        $key = 'posts_' . $id;
        return Cache::remember($key, 1 * 60, function () use ($id, $columns) {
            return $this->post->select($columns)->find($id);
        });
    }

    public function getByManyId(array $ids, array $columns = ['*'], callable $callback = null)
    {
        $query = $this->post->select($columns)->whereIn('id', $ids);
        if ($query) {
            $query = $callback($query);
        }
        return $query->get();
    }

    public function addViews(Post $post)
    {
        $post->increment('views');
        if ($post->save()) {
            // 将当前文章浏览数 +1，存储到对应 Sorted Set 的 score 字段
            Redis::zincrby('popular_posts', 1, $post->id);
        }
        return $post->views;
    }

    // 热门文章排行榜
    public function trending($num = 10)
    {
        $cacheKey = $this->trendingPostsKey . '_' . $num;

        return Cache::remember($cacheKey, 3, function () use ($num){
            $postIds = Redis::zrevrange('popular_posts', 0, $num - 1);
            if (!$postIds) {
                return [];
            }
            $idsStr = implode(',', $postIds);
            return $this->getByManyId($postIds, ['*'], function ($query) use ($idsStr) {
                return $query->orderByRaw('field(`id`, ' . $idsStr . ')');
            });
        });
    }

    public function addViewsQueue(Post $post)
    {
        Redis::rpush('popular_posts_queue', $post->id);
        return ++$post->views;
    }
}
