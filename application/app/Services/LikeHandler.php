<?php


namespace App\Services;


use App\Like;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

class LikeHandler
{

    /**
     * Gets likes (Cache and if not available, then from the DB)
     * @param $commentIds
     *
     * @return array
     */
    public function getLikes($commentIds)
    {
        $groups      = [];
        $groupsFull  = [];
        $idsNotFound = [];
        foreach ($commentIds as $id) {
            $cacheData = $this->getCacheLikes($id);

            if ($cacheData) {
                $groupsFull[$id] = $cacheData;
                $groups[$id]     = array_map(
                    function ($item) {
                        return $item->user_id;
                    },
                    is_array($cacheData) ? $cacheData : (array)$cacheData
                );
            } else {
                $idsNotFound[] = $id;
            }
        }

        if (count($idsNotFound)) {
            $likes = $this->getDbLikes($idsNotFound);

            foreach ($likes as $like) {
                if ( ! isset($groups[$like['comment_id']])) {
                    $groups[$like['comment_id']]     = [];
                    $groupsFull[$like['comment_id']] = [];
                }
                $groups[$like['comment_id']][]     = $like['user_id'];
                $groupsFull[$like['comment_id']][] = $like;
            }


            foreach ($idsNotFound as $notFoundId) {
                if (isset($groupsFull[$notFoundId])) {
                    $this->saveLikeToCache($notFoundId, $groupsFull[$notFoundId]);
                }
            }
        }


        return $groups;
    }

    /**
     * Saved a like data to cache
     * @param $key
     * @param $data
     */
    public function saveLikeToCache($key, $data)
    {
        Redis::set($this->getCacheName($key), json_encode($data), 'EX', 60 * 10);
    }

    /**
     * Gets Likes from DB
     * @param $ids
     *
     * @return array
     */
    public function getDbLikes($ids)
    {
        return Like::all()->whereIn('comment_id', $ids)->toArray();
    }

    /**
     * Gets cached likes
     * @param $id
     *
     * @return array|mixed|null
     */
    public function getCacheLikes($id)
    {
        $data = Redis::get($this->getCacheName($id));
        if ($data) {
            $outData = json_decode($data);

            return is_array($outData) ? $outData : (array)$outData;
        }

        return null;
    }

    /**
     * Used to toggle like status and updated toInsert or toDelete
     * @param $comment
     * @param $userId
     *
     * @return array
     * @throws \Exception
     */
    public function toggleLike($comment, $userId)
    {
        $cacheData = $this->getCacheLikes($comment['id']);
        if ( ! $cacheData) {
            $cacheData = [];
        } elseif (is_object($cacheData)) {
            $cacheData = (array)$cacheData;
        }

        $doInsert = true;

        foreach ($cacheData as $idx => $datum) {
            if ($datum->user_id == $userId) {
                $doInsert = false;
                $this->markForDelete($datum->id);
                unset($cacheData[$idx]);
                $this->saveLikeToCache($comment['id'], array_values($cacheData));

                break;
            }
        }

//        dd($doInsert);
        if ($doInsert) {
            $like = new Like([
                'id'         => (string)Uuid::uuid4(),
                'type'       => 'comment',
                'comment_id' => $comment['id'],
                'user_id'    => $userId
            ]);

            $cacheData[] = $like->toArray();

            $this->markForInsert($like);

            $this->saveLikeToCache($comment['id'], $cacheData);
        }

        $outData = array_map(
            function ($item) {
                if (is_array($item)) {
                    return $item['user_id'];
                } else {
                    return $item->user_id;
                }
            },
            $cacheData
        );

        return array_values($outData);
    }

    /**
     * Marks a like for deletion
     * @param $id
     */
    public function markForDelete($id)
    {
        $toDelete = $this->getCacheLikes('toDelete');
        if ( ! $toDelete) {
            $toDelete = [];
        }
        $toDelete[] = $id;
        $this->saveLikeToCache('toDelete', $toDelete);
    }

    /**
     * Marks a like for Insert
     * @param $like
     */
    public function markForInsert($like)
    {
        $toInsert = $this->getCacheLikes('toInsert');
        if ( ! $toInsert) {
            $toInsert = [];
        } else {
            $toInsert = (array)$toInsert;
        }
        $toInsert[$like->id] = $like->toArray();
        $this->saveLikeToCache('toInsert', $toInsert);
    }

    /**
     * Unmarks a like to deletion
     * @param $id
     */
    public function unmarkForDelete($id)
    {
        $toDelete = $this->getCacheLikes('toDelete');
        if ( ! $toDelete) {
            $toDelete = [];
        }
        unset($toDelete[$id]);
        $this->saveLikeToCache('toDelete', $toDelete);
    }

    /**
     * Used to formalize the names used to cache keys
     * @param $key
     *
     * @return string
     */
    private function getCacheName($key)
    {
        return sprintf('Like:%s', $key);
    }


}
