<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['owner', 'member', 'viewer'];
        foreach ($roles as $name) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $name],
                ['name' => ucfirst($name), 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
