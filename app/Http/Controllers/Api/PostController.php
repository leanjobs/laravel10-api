<?php

namespace App\Http\Controllers\Api;

//import Model "Post" 
use App\Models\Post;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//import Resource "PostResource" 
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;

//import Facade "Validator" 
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\RedirectResponse;



class PostController extends Controller
{
    /** 
     * index 
     * 
     * @return void 
     */
    public function index()
    {
        //get all posts 
        $posts = Post::latest()->paginate(5);

        //return collection of posts as a resource 
        return new PostResource(true, 'List Data Posts', $posts);
    }

    /** 
     * store 
     * 
     * @param  mixed $request 
     * @return void 
     */
    public function store(Request $request)
    {
        //define validation rules 
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required | min:10',
            'content'   => 'required | min:10',
        ]);

        //check if validation fails 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image 
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post 
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        //return response 
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', $post);
    }

    public function show($id)
    {

        //find post by id
        $post = Post::find($id);

        //return single post as a resource
        return new PostResource(true, 'detail data post!', $post);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title'     => 'required | min:10',
            'content'   => 'required | min:10',
        ]);

        //check validator
        if ($validator->fails()) {
            return Response()->json($validator->errors(), 422);
        }

        //get id
        $post = Post::find($id);

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts' . basename($post->image));

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {
            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        //return response
        return new PostResource(true, 'detail data post!', $post);
    }

    public function destroy($id)
    {

        //get post by ID
        $post = Post::find($id);

        //delete image
        Storage::delete('public/posts/' . basename($post->image));

        //delete post
        $post->delete();

        return new PostResource(true, 'detail data dihapus!', $post);
    }
}
