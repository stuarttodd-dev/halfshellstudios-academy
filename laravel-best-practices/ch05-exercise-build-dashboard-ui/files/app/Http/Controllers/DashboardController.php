<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_posts' => Post::count(),
            'draft_posts' => Post::query()->where('is_published', false)->count(),
            'published_posts' => Post::query()->where('is_published', true)->count(),
        ];

        $recentPosts = Post::query()->latest()->take(10)->get();

        return view('dashboard.index', [
            'stats' => $stats,
            'recentPosts' => $recentPosts,
        ]);
    }
}
