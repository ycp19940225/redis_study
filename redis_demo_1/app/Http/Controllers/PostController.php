<?php

namespace App\Http\Controllers;

use App\Events\Postviewed;
use App\Jobs\ImageUploadProcessor;
use App\Models\Post;
use App\Repository\PostRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
{
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
        // 需要登录认证后才能发布文章
        $this->middleware('auth')->only(['create', 'store']);
    }
    //
    public function show($id)
    {
        // 定义一个单位时间内限定请求上限的限流器，每秒最多支持 100 个请求
        return Redis::throttle("posts.${id}.show.concurrency")
            ->allow(100)->every(1)
            ->then(function () use ($id) {
                // 正常访问
                $post = $this->postRepository->getById($id);
                event(new PostViewed($post));
                return view('posts.show', ['post' => $post]);
            }, function () {
                // 触发并发访问上限
                abort(429, 'Too Many Requests');
            });
    }

    public function popular()
    {
        $posts = $this->postRepository->trending(10);
        if ($posts) {
            dump($posts->toArray());
        }
    }

    // 文章发布页面
    public function create()
    {
        return view('posts.create');
    }

// 文章发布处理
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string|min:10',
            'image' => 'required|image|max:1024'  // 尺寸不能超过1MB
        ]);

        $post = new Post($data);
        $post->user_id = $request->user()->id;
        try {
            if ($post->save()) {
                $image = $request->file('image');
                // 获取图片名称
                $name = $image->getClientOriginalName();
                // 获取图片二进制数据后通过 Base64 进行编码
                $content = base64_encode($image->getContent());
                // 通过图片处理任务类将图片存储工作推送到 uploads 队列异步处理
                ImageUploadProcessor::dispatch($name, $content, $post)->onQueue('uploads');
                return redirect('posts/' . $post->id);
            }
            return back()->withInput()->with(['status' => '文章发布失败，请重试']);
        } catch (QueryException $exception) {
            return back()->withInput()->with(['status' => '文章发布失败，请重试']);
        }
    }
}
