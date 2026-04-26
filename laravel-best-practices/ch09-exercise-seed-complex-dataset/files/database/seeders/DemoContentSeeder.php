<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Illustrates the *structure* from the lesson. Wire to your real models
 * (Organisation, User, Project, Task, Tag) once migrations match.
 */
class DemoContentSeeder extends Seeder
{
    public const int DEMO_TASKS_PER_PROJECT = 8;

    public function run(): void
    {
        // Example: one org’s graph, all-or-nothing.
        DB::transaction(function () {
            // $org = Organisation::factory()->create();
            // $owner = User::factory()->for($org)->create([...]);
            // $owner->assignRole('owner');
            // Project::factory()->count(2)->for($org)->has(
            //     Task::factory()->count(self::DEMO_TASKS_PER_PROJECT)
            // )->create();
        });
    }
}
