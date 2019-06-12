<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Cache;
use App\User;
class profilesController extends Controller
{
    
    public function index(User $user)
    {
        $follows=(auth()->user()) ? auth()->user()->following->contains($user): false;
        //find will return a correct user with an id in
        //the db but findOrfail will return a result n handle users without n id with a 
        //proper message
        //dd (die and dump) will echo out the output and stop all operation
       // $user=\App\User::findOrfail($user);
       $postCount = Cache::remember(
        'count.posts.' . $user->id,
        now()->addSeconds(30),
        function () use ($user) {
            return $user->posts->count();
        });
    $followersCount = Cache::remember(
        'count.followers.' . $user->id,
        now()->addSeconds(30),
        function () use ($user) {
            return $user->profile->followers->count();
        });
    $followingCount = Cache::remember(
        'count.following.' . $user->id,
        now()->addSeconds(30),
        function () use ($user) {
            return $user->following->count();
        });
        return view('profiles.index',compact('user','follows','postCount','followersCount','followingCount'));
         //it can also be profiles/index instead of the above
    }
    public function edit(\App\User $user)
    {
        //authorize user to do edit on profile 
        //meaning login is a must it'll b hidden if not login in
        $this->authorize('update',$user->profile);
        return view('profiles.edit', compact('user'));
    }
    public function update(User $user)
    {  
     $this->authorize('update', $user->profile);
        $data = request()->validate([
            'title' => 'required',
            'desc' => '',
            'url' => 'url',
            'image' => '',
        ]);
        if (request('image')) {
            $imagePath = request('image')->store('profile', 'public');
            $image = Image::make(public_path("storage/{$imagePath}"))->fit(750, 750);
            $image->save();
            $imageArray = ['image' => $imagePath];
        }
        auth()->user()->profile->update(array_merge(
            $data,
            $imageArray ?? []
        ));
        return redirect("/profile/{$user->id}");
    
    
    }
}
