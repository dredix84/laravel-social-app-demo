<?php


namespace App\Services;


use App\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PostRetriever
{
    /**
     * @return mixed
     */
    public static function latest()
    {
        $posts     = Redis::get('Post:latest');
        $fromCache = true;
        if ($posts) {
            $posts = json_decode($posts);
        } else {
            $posts = Post::latest()->with('comments')->get()->toArray();
            self::set($posts);

            $fromCache = false;
        }
        return $posts;
    }

    /**
     * Used to save/set data to cache
     * @param $posts
     * @param  bool  $restoreExpiry
     */
    public static function set($posts, $restoreExpiry = false){
        Redis::set('Post:latest', json_encode($posts), 'EX', config('app.redisDataLife', 600));
    }
}
