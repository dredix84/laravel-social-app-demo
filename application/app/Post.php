<?php

namespace App;

use App\Services\PostRetriever;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class Post extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'image_url',
        'body',
        'user_id',
        'created_at'
    ];

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'DESC');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id', 'id')->orderBy('created_at', 'DESC');;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $writeToDb = env('CACHE2DB', false);    //Used to determine is data should be written to database

            if ( ! isset($model->updated_at)) {
                $model->id    = Uuid::uuid4();
            }

            if ( ! $writeToDb) {
                Log::debug('Creating  POST  ');
                $model->comments   = [];
                $model->created_at = Carbon::now();

                $post = PostRetriever::latest();
                array_unshift($post, $model->toArray());
                PostRetriever::set($post);

                return false;
            }
        });
    }

}
