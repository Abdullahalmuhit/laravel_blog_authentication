<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Session, Exception;

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
            'file' => 'mimes:jpeg,jpg,png,gif|required'
        ]);
        try {
            DB::beginTransaction();
        $post = new Post();
        $post->title = $request->title;
        $post->description = $request->description;
        if ($request->hasFile('file')) {
            $time = time();
            $file = $request->file;
            $file->storeAs('public/images', $time . $file->getClientOriginalName());
            $post->file = $time . $file->getClientOriginalName();
        }
        $post->save();
        DB::commit();
        return redirect()->route('posts.index')
                    ->with('success','Post created successfully.');
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('alert-danger', 'Something went wrong!');
            return redirect()->back();
        }
    }

    public function show(Post $post)
    {
        return view('posts.show',compact('post'));
    }

    public function edit(Post $post)
    {
        return view('posts.edit',compact('post'));
    }

    public function update(Request $request, $id)
    {
    
        request()->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'mimes:jpeg,jpg,png,gif|required'
        ]);

        try {
            DB::beginTransaction();
        
            $post = Post::findOrFail($id); 
            $post->title = $request->title;
            $post->description = $request->description;

            if ($request->hasFile('file')) {
                $image_del = Post::where('id', $id)->first();
                if (isset($image_del->file)) {
                    $file_name_to_delete = $image_del->file;
                    if (Storage::disk('public')->exists('/images/' . $file_name_to_delete)) {
                        if ($file_name_to_delete != NULL) {
                            Storage::delete('public/images/' . $file_name_to_delete);
                        }
                    }
                }
                $time = time();
                $file = $request->file;
                $file->storeAs('public/images', $time . $file->getClientOriginalName());
                $post->file = $time . $file->getClientOriginalName();
            }
            $post->save();
            DB::commit();
            return redirect()->route('posts.index')
                        ->with('success','Post updated successfully');

        } catch (Exception $e) {
            DB::rollback();
            Session::flash('alert-danger', 'Something went wrong!');
            return redirect()->back();
        }
    }

    public function destroy(Post $post)
    {
        $post->delete();
    
        return redirect()->route('posts.index')
                        ->with('success','Post deleted successfully');
    }
}
