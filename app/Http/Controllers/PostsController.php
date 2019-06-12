<?php

namespace App\Http\Controllers;
use App\Post;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class PostsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        //pluck gets only the user id time 3:55:00
        $users = auth()->user()->following()->pluck('profiles.user_id');
        //wherein meaning where the list of user id is in the list of users retrieved
        $posts = Post::whereIn('user_id', $users)->with('user')->latest()->paginate(5);
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store()
    {
        $data=request()->validate([
            'caption'=>'required',
            'image'=>['required','image'],

        ]);
        /*
        ways of storing values
        \App\Post::create([
            'caption'=>$data['caption]

        ]);
        */
        $imagePath=request('image')->store('uploads','public');
        $image = Image::make(public_path("storage/{$imagePath}"))->fit(1200, 1200);
        $image->save();

        auth()->user()->posts()->create([
            'caption'=>$data['caption'],
        
            'image'=>$imagePath,
        ]);
        
        return redirect('/profile/' . auth()->user()->id);
        //dd(request()->all());
    
    }
    public function show(\App\Post $post)
    {
       /* dd($post);
       return view('posts.show',[
           'post'=>$post,
       ]);*/
       //short cut for the above code
       return view('posts.show',compact('post'));
    }
}
 