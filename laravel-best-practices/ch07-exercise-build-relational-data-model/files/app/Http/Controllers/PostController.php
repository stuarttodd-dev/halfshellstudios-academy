<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->select(['id', 'user_id', 'title', 'slug', 'published_at'])
            ->with([
                'author:id,name',
                'tags:id,name,slug',
            ])
            ->withCount('comments')
            ->latest('published_at')
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }
}
