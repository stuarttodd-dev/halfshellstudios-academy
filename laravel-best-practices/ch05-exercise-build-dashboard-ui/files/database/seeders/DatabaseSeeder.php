<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::factory()->create();
        Post::query()->create([
            'user_id' => $u->id,
            'title' => 'Hello',
            'slug' => 'hello',
            'body' => 'Body',
            'is_published' => true,
            'published_at' => now(),
        ]);
        Post::query()->create([
            'user_id' => $u->id,
            'title' => 'Draft',
            'slug' => 'draft',
            'body' => 'WIP',
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
