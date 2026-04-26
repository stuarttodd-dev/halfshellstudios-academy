<?php

namespace App\Policies;

use App\Enums\OrgRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->organisation_id === $project->organisation_id;
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->organisation_id !== $project->organisation_id) {
            return false;
        }

        return match ($user->org_role) {
            OrgRole::Editor, OrgRole::Admin => true,
            default => false,
        };
    }

    public function delete(User $user, Project $project): bool
    {
        if ($user->organisation_id !== $project->organisation_id) {
            return false;
        }

        return $user->org_role === OrgRole::Admin;
    }
}
