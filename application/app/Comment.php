<?php

namespace App;

use App\Services\CommentHandler;
use App\Services\PostRetriever;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class Comment extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'body',
        'user_id'
    ];

    protected $casts = [
        'post_id' => 'string',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $writeToDb = env('CACHE2DB', false);    //Used to determine is data should be written to database

            if ( ! isset($model->updated_at)) {
                $model->id = Uuid::uuid4();
            }

            if ( ! $writeToDb) {
                Log::debug('Creating Comment');
                $model->created_at = Carbon::now();
                $commentHandler    = new CommentHandler();
                $commentHandler->add($model);

                return false;
            }
        });
    }
}
