<?php

namespace App\Console\Commands;

use App\Comment;
use App\Post;
use App\Services\CommentHandler;
use App\Services\PostRetriever;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DbSyncFromCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:save-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Saves cache data to database';

    /**
     * @var CommentHandler
     */
    private $commentHandler;


    /**
     * Create a new command instance.
     *
     * @param  CommentHandler  $commentHandler
     */
    public function __construct(CommentHandler $commentHandler)
    {
        parent::__construct();
        $this->commentHandler = $commentHandler;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        putenv("CACHE2DB=true");
        $this->syncCacheToDb();
        $this->deleteComments();
    }

    /**
     * Used to delete comments from the database
     */
    protected function deleteComments()
    {
        $this->info('Task: Delete comments');
        try {
            $toDelete = $this->commentHandler->getToDelete();
            if ($toDelete) {
                Comment::destroy($toDelete);
                $this->commentHandler->setToDelete([]);
                $message = 'COMMAND: db:save-cache Deleting comments marked for deletion. Count: '.count($toDelete);
                Log::debug($message);
                $this->info($message);
            }
        } catch (\Exception $e) {
            Log::error(
                "COMMAND: db:save-cache Error while trying deleted comments marked for deletion. ",
                [
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine()
                ]
            );
        }
    }

    /**
     * Used to sync the unsaved data from cache to the database
     * @throws \Exception
     */
    protected function syncCacheToDb()
    {
        $this->info('Task: Insert new post into DB');
        try {
            $newPost     = 0;
            $newComments = 0;
            $cachePost   = PostRetriever::latest();
            $this->info('Task: Cache count: '.count($cachePost));

            if ($cachePost && count($cachePost)) {
                foreach ($cachePost as &$post) {

                    $comments = $post->comments;

                    //Saving post
                    if ( ! isset($post->updated_at)) {
                        $data2Save = (array)$post;
                        unset($data2Save['saved']);
                        Log::info('$data2Save: ', $data2Save);
                        $post = Post::create($data2Save);

                        $this->info("POST ID: ".$post->id);
                        $newPost++;
                    }

                    //Saving comments
                    foreach ($comments as &$comment) {
                        if ( ! isset($comment->updated_at)) {
                            $comment->post_id = $post->id;
                            Comment::create((array)$comment);
                            $comment->updated_at = Carbon::now();
                            $newComments++;
                        }
                    }
                    $post->comments = $comments;


                    if (($newPost % 10) == 0) { //Update cache with id, created_at and modified_at after every new 10 post
                        //TODO: set orignial expiry
                        PostRetriever::set($cachePost);
                    }
                }


                if ($newPost) {
                    //TODO: set orignial expiry
                    PostRetriever::set($cachePost);
                }

                $message = 'COMMAND: db:save-cache New post written to db: '.$newPost;
                $message .= "\nCOMMAND: db:save-cache New comment written to db: ".$newComments;
                Log::debug($message);
                $this->info($message);
            }
        } catch (\Exception $e) {
            throw $e;
            Log::error($e->getMessage());
            Log::error(
                "COMMAND: db:save-cache Error while trying to written to db.",
                [
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

}
