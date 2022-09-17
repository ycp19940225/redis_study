<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
{
    //
    public function show(Post $post)
    {
        $post->increment('views');
        if($post->save()){
            Redis::zincrby('popular_posts', 1, $post->id);
        }

        return 'show post id :' . $post->id;
    }

    public function popular()
    {
        $postIds = Redis::zrevrange('popular_posts', 0 , 9);
        if(!empty($postIds)){
            $postIdsStr= implode(',', $postIds);
            $postInfo = Post::whereIn('id', $postIds)
                ->select(['id', 'title', 'views'])
                ->orderBYRaw("field(`id`, '".$postIdsStr."')")
                ->get()->toArray();
        }else{
            $postInfo = [];
        }

        dd($postInfo);
    }
}
