<?php

declare(strict_types=1);

use App\Enums\OrgRole;
use App\Models\Organisation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

if (app()->isLocal()) {
    Route::get('/_exercise/login', function () {
        $org = Organisation::query()->firstOrCreate(
            ['name' => 'Exercise Org'],
            ['name' => 'Exercise Org'],
        );
        $user = User::query()->updateOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Editor',
                'password' => Hash::make('password'),
                'organisation_id' => $org->id,
                'org_role' => OrgRole::Editor,
            ],
        );
        $project = Project::query()->firstOrCreate(
            [
                'organisation_id' => $org->id,
                'title' => 'Example project',
            ],
            [
                'user_id' => $user->id,
            ],
        );
        Auth::login($user);

        return 'Logged in (local, APP_ENV=local). Use cookie jar in README curls for /projects. Project id: '.$project->id;
    });
}
