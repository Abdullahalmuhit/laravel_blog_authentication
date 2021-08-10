<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:post-list|post-create|post-edit|post-delete', ['only' => ['index','show']]);
         $this->middleware('permission:post-create', ['only' => ['create','store']]);
         $this->middleware('permission:post-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:post-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return view('posts.index',compact('posts'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        request()->validate([
            'title' => 'required',
            'description' => 'required',
        ]);
    
        Post::create($request->all());
    
        return redirect()->route('posts.index')
                        ->with('success','Post created successfully.');
    }

    public function show(Post $post)
    {
        return view('posts.show',compact('post'));
    }

    public function edit(Post $post)
    {
        return view('posts.edit',compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        request()->validate([
            'title' => 'required',
            'description' => 'required',
        ]);
    
        $post->update($request->all());
    
        return redirect()->route('posts.index')
                        ->with('success','Post updated successfully');
    }

    public function destroy(Post $post)
    {
        $post->delete();
    
        return redirect()->route('posts.index')
                        ->with('success','Post deleted successfully');
    }
}
