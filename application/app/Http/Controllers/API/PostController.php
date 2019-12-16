<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Post;
use App\Services\PostRetriever;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
//        return Post::latest()->with('comments')->get();
        return PostRetriever::latest();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return array
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'title'     => 'required|string|max:150',
                'body'      => 'required|string',
                'image_url' => 'nullable|url',
            ]
        );

        return Post::create([
            'title'     => $request['title'],
            'image_url' => $request['image_url'],
            'body'      => $request['body'],
            'user_id'   => Auth::user()->id
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return array
     */
    public function show($id)
    {
        $post = Post::where('id', $id)->with('comments')->get();

        return count($post) ? $post[0] : null;
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
        return [
            'id' => $id,
        ];
    }
}
