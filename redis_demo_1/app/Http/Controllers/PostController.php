<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Repository\PostRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
{
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }
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
        $posts = $this->postRepository->trending(10);
        if ($posts) {
            dump($posts->toArray());
        }
    }
}
