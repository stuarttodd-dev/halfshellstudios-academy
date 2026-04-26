<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);
        $projects = Project::query()
            ->where('organisation_id', $request->user()->organisation_id)
            ->get();

        return response($projects);
    }

    public function show(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        return response($project);
    }

    public function update(Request $request, Project $project): Response
    {
        $this->authorize('update', $project);
        $data = $request->validate(['title' => ['sometimes', 'string', 'max:255']]);
        $project->update($data);

        return response($project->fresh());
    }

    public function destroy(Request $request, Project $project): Response
    {
        $this->authorize('delete', $project);
        $project->delete();

        return response()->noContent();
    }
}
