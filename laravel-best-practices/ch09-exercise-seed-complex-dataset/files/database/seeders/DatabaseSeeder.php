<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'staging')) {
            return;
        }

        $this->call([
            RoleSeeder::class,
            // OrganisationSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
