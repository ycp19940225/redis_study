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
    public function show($id)
    {
        $post = $this->postRepository->getById($id);
        $views = $this->postRepository->addViewsQueue($post);
        return "Show Post #{$post->id}, Views: {$views}";
    }

    public function popular()
    {
        $posts = $this->postRepository->trending(10);
        if ($posts) {
            dump($posts->toArray());
        }
    }
}
