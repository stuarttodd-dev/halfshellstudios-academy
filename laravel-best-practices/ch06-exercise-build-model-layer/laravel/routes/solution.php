<?php

declare(strict_types=1);

use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/posts-demo', fn () => ['count' => Post::count()]);
