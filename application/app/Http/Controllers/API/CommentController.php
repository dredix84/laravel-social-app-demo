<?php

namespace App\Http\Controllers\API;

use App\Comment;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteCommentJob;
use App\Services\CommentHandler;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Comment[]|Collection
     */
    public function index()
    {
        return Comment::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'post_id' => 'required',
                'body'    => 'required|string'
            ]
        );

        return Comment::create([
            'post_id' => $request['post_id'],
            'body'    => $request['body'],
            'user_id' => Auth::user()->id
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function show($id)
    {
        $comment   = Redis::get('comment:'.$id);
        $fromCache = true;
        if ($comment) {
            $comment = json_decode($comment);
        } else {
            $comment = Comment::where('id', $id)->get()->toArray();

            Redis::set('comment:'.$id, json_encode($comment), 'EX', 10);
            $fromCache = false;
        }

        return compact('comment', 'fromCache');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     *
     * @return Response
     */
    public
    function update(
        Request $request,
        $id
    ) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @param  CommentHandler  $commentHandler
     * @param  int  $id
     *
     * @return array
     * @throws ValidationException
     */
    public function destroy(Request $request, CommentHandler $commentHandler, $id)
    {
        $this->validate(
            $request,
            [
                'post_id' => 'required',
                'id'      => 'required',
            ]
        );

        $comment = $commentHandler->getCommentByKeyAndPost($request['id'], $request['post_id']);
        if ($comment && Auth::user()->id != $comment->user_id) {
            return \response('User not the owner of comment', 403);
        }
        $commentHandler->delete($comment);

        return [
            'id' => $id,
        ];
    }
}
