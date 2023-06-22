<?php

namespace App\Http\Controllers\Api;

// Import Model
use App\Models\Post;

// Import Resource "PostResource"
use App\Http\Resources\PostResource;

// import facade "Validator"
use Illuminate\Support\Facades\Validator;

// import facade "Storage"
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(){
        // get all post
        $posts = Post::latest()->paginate(5);

        // return collection
        return new PostResource(status: true, message: 'List Data Posts', resource: $posts);
    }

    public function store(Request $request){
        // define validator rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // create post
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        return new PostResource(status: true, message: 'Data Post Berhasil Ditambahkan!', resource: $post);
    }

    public function show($id){

        // find post by id
        $post= Post::find($id);

        return new PostResource(status: true, message: 'Detail Data Post!', resource: $post);
    }

    public function update(Request $request, $id){
        // rules
        $validator = Validator::make($request->all(),[
            'title'     => 'required',
            'content'   => 'required',
        ]);

        // check validator
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        // find post by id
        $post = Post::find($id);

        // Check gambar jika tidak kosong
        if($request->hasFile('image')){
            // upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // Hapus Gambar Lama tes
            Storage::delete('public/posts/'. basename($post->image));

            // ganti gambar / upload gambar baru
            $post->update([
                'image'         => $image->hashName(),
                'title'         => $request->title,
                'content'       => $request->content,
            ]);
        } else {
            // update post tanpa gambar
            $post->update([
                'title'         => $request->title,
                'content'       => $request->content
            ]);
        }

        return new PostResource(status: true, message: 'Data Post Berhasil Diubah!', resource: $post);
    }

    public function destroy($id){
        // find post by id
        $post = Post::find($id);

        // Hapus Gambar
        Storage::delete('public/posts/'. basename($post->image));

        // delete data
        $post->delete();

        return new PostResource(true, 'Data Post Berhasil Dihapus!', $post);
    }
}
