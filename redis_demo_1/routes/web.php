<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/posts/popular', [PostController::class, 'popular']);
    Route::get('/posts/{id}', [PostController::class, 'show'])->where('id', '[0-9]+');


Route::get('/connection', function () {
    dd(\Illuminate\Support\Facades\Redis::connection());
});

Route::get('/site_visits', function () {
    return '网站全局访问量：' . \Illuminate\Support\Facades\Redis::get('site_total_visits');
});
Route::get('/', function () {
    return view('welcome');
});
