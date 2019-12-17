<?php

namespace App\Services;


use App\Comment;
use App\Jobs\DeleteCommentJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CommentHandler
{
    /**
     * Used to delete a comment from the cache and also adds the comment to the queue to be deleted
     *
     * @param $comment
     */
    public function delete($comment)
    {
        try {
            $this->deleteCommentFromCache($comment);

            $deletes = self::getToDelete();
            if ( ! $deletes) {
                $deletes = [];
            }
            $deletes[] = $comment->id;

            self::setToDelete($deletes);
        } catch (\Exception $exception) {
            Log::error(
                "Error in CommentHandler while attempting to delete comment from cache",
                [
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage()
                ]
            );
        } finally {
            DeleteCommentJob::dispatch($comment->id);
        }
    }

    /**
     * Used to add a comment to the cached comments
     * @param $comment
     *
     * @return bool
     */
    public function add(&$comment)
    {
        $allPost = PostRetriever::latest();

        $postId = $comment->post_id;
        foreach ($allPost as &$post) {
            if ($post->id == $postId) {
                array_unshift($post->comments, $comment->toArray());
                PostRetriever::set($allPost, true);

                return true;
            }
        }

        return false;
    }

    /**
     * Used to remove a comment from the cache
     * @return mixed|null
     */
    public function getToDelete()
    {
        $data = Redis::get('Comment:toDelete');

        return $data ? json_decode($data) : null;
    }

    /**
     * Used to mark data for deletion
     * @param $data
     */
    public function setToDelete($data)
    {
        Redis::set('Comment:toDelete', json_encode($data));
    }

    /**
     * Used to get a comment from cache by the ID and post ID
     *
     * @param $commentKeyId
     * @param $postId
     *
     * @return array|null
     */
    public function getCommentByKeyAndPost($commentKeyId, $postId)
    {
        $allPost = PostRetriever::latest();

        foreach ($allPost as $post) {
            if ($post->id == $postId) {
                foreach ($post->comments as $comment) {
                    if ($comment->id == $commentKeyId) {
                        return $comment;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Used to delete a comment from the cache
     *
     * @param $comment
     *
     * @return bool
     */
    protected function deleteCommentFromCache($comment)
    {
        $allPost = PostRetriever::latest();

        $postId    = &$comment->post_id;
        $commentId = &$comment->id;

        foreach ($allPost as &$post) {
            if ($post->id == $postId) {
                $post->comments = (array)$post->comments;
                foreach ($post->comments as $idx => $comment) {
                    if ($comment->id == $commentId) {
                        unset($post->comments[$idx]);
                        PostRetriever::set($allPost, true);

                        return true;
                    }
                }
            }
        }

        return false;
    }
}
