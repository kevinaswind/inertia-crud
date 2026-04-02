<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');

        $posts = Auth::user()
            ->posts()
            ->when($search, function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Post $post) => $this->serializePost($post));

        return inertia()->render('posts/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia()->render('posts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        Auth::user()->posts()->create($data);
        Inertia::flash('message', 'Post created successfully!');
        return to_route('posts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 404);

        return inertia()->render('posts/Show', [
            'post' => $this->serializePost($post),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 404);

        return inertia()->render('posts/Edit', [
            'post' => $this->serializePost($post),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 404);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }

            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        Inertia::flash('message', 'Post updated successfully!');

        return to_route('posts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 404);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        Inertia::flash('message', 'Post deleted successfully!');

        return to_route('posts.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializePost(Post $post): array
    {
        $payload = $post->toArray();
        $payload['image'] = $post->image ? Storage::url($post->image) : null;

        return $payload;
    }
}
