<?php

namespace Tests\Feature;

use App\Enums\OrgRole;
use App\Models\Organisation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_cannot_delete_project(): void
    {
        $org = Organisation::query()->create(['name' => 'A Ltd']);
        $editor = User::factory()->for($org)->create(['org_role' => OrgRole::Editor]);
        $project = Project::query()->create([
            'organisation_id' => $org->id,
            'user_id' => $editor->id,
            'title' => 'Docs',
        ]);
        $this->actingAs($editor)
            ->deleteJson(route('projects.destroy', $project))
            ->assertStatus(403);
    }
}
