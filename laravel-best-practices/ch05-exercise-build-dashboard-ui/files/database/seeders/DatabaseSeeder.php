<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::query()->where('email', 'dashboard-seed@example.com')->first()
            ?? User::factory()->create([
                'name' => 'Dashboard user',
                'email' => 'dashboard-seed@example.com',
            ]);

        Post::query()->updateOrCreate(
            ['slug' => 'hello'],
            [
                'user_id' => $u->id,
                'title' => 'Hello',
                'body' => 'Body',
                'is_published' => true,
                'published_at' => now(),
            ],
        );
        Post::query()->updateOrCreate(
            ['slug' => 'draft'],
            [
                'user_id' => $u->id,
                'title' => 'Draft',
                'body' => 'WIP',
                'is_published' => false,
                'published_at' => null,
            ],
        );
    }
}
