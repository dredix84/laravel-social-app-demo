<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Like;
use App\Services\LikeHandler;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  LikeHandler  $likeHandler
     * @param  Request  $request
     *
     * @return ResponseFactory|Response
     */
    public function index(LikeHandler $likeHandler, Request $request)
    {
        if (isset($request['comment_ids'])) {
            $data =  $likeHandler->getLikes(explode(',', $request['comment_ids']));
            return count($data) ? $data : '{}';
        } else {
            return response('No ids passed', 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  LikeHandler  $likeHandler
     * @param  Request  $request
     *
     * @return Response
     * @throws ValidationException
     */
    public function store(LikeHandler $likeHandler, Request $request)
    {
        $this->validate(
            $request,
            [
                'id' => 'required'
            ]
        );

        return $likeHandler->toggleLike($request->all(), Auth::user()->id);
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
