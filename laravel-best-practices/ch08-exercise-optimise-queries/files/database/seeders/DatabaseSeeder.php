<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::query()->where('email', 'buyer@example.com')->first()
            ?? User::factory()->create(['email' => 'buyer@example.com']);

        if (Order::query()->where('user_id', $u->id)->exists()) {
            return;
        }

        Order::query()->insert([
            ['user_id' => $u->id, 'status' => 'paid', 'total' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $u->id, 'status' => 'paid', 'total' => 20, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
