<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\SyncProjectPersonsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Department;
use App\Models\PersonAffiliation;
use App\Models\Project;
use App\Models\ProjectAffiliation;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user && $user->can('view-projects'), 403);

        $query = Project::query()->with(['department.organization', 'admin']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        if (!$request->boolean('include_inactive', false)) {
            $query->where('is_active', true);
        }

        if (!$user->hasRole('Super Admin')) {
            $query->whereIn('department_id', $this->allowedDepartmentIds($user));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreProjectRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        abort_unless(
            $user->hasRole('Super Admin') || in_array($data['department_id'], $this->allowedDepartmentIds($user), true),
            403,
            'You cannot create projects for this department.'
        );

        $project = Project::create($data);

        return response()->json($project->load(['department.organization', 'admin']), 201);
    }

    public function show(Project $project, Request $request)
    {
        $this->authorizeProjectView($request->user(), $project);

        return response()->json(
            $project->load([
                'department.organization',
                'admin',
                'affiliations.person',
            ])
        );
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $user = $request->user();
        $data = $request->validated();

        $this->authorizeProjectAdminAction($user, $project);

        abort_unless(
            $user->hasRole('Super Admin') || in_array($data['department_id'], $this->allowedDepartmentIds($user), true),
            403,
            'You cannot move this project to that department.'
        );

        $project->update($data);

        return response()->json($project->load(['department.organization', 'admin']));
    }

    public function destroy(Project $project, Request $request)
    {
        $user = $request->user();

        abort_unless($user && $user->can('delete-projects'), 403);
        $this->authorizeProjectAdminAction($user, $project);

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }

    public function syncPersons(SyncProjectPersonsRequest $request, Project $project)
    {
        $user = $request->user();

        $this->authorizeProjectPersonsAction($user, $project);

        $payload = collect($request->validated()['affiliations'])->map(function (array $item) use ($user, $project) {
            $isStaff = $item['affiliation_type'] === 'staff';

            if ($isStaff) {
                $personOrgIds = PersonAffiliation::query()
                    ->where('person_id', $item['person_id'])
                    ->where('status', 'active')
                    ->pluck('organization_id')
                    ->all();

                if (!in_array($project->department->organization_id, $personOrgIds, true)) {
                    abort(422, 'Staff must belong to the same organization as the project department.');
                }
            }

            return [
                'project_id' => $project->id,
                'person_id' => $item['person_id'],
                'affiliation_type' => $item['affiliation_type'],
                'role_title' => $item['role_title'] ?? null,
                'occupation' => $item['occupation'] ?? null,
                'start_date' => $item['start_date'] ?? null,
                'end_date' => $item['end_date'] ?? null,
                'status' => $item['status'] ?? 'active',
                'updated_by' => $user->id,
                'created_by' => $user->id,
            ];
        });

        foreach ($payload as $item) {
            ProjectAffiliation::query()->updateOrCreate(
                [
                    'project_id' => $item['project_id'],
                    'person_id' => $item['person_id'],
                    'affiliation_type' => $item['affiliation_type'],
                ],
                $item
            );
        }

        return response()->json(
            $project->fresh()->load(['affiliations.person'])
        );
    }

    private function authorizeProjectView($user, Project $project): void
    {
        abort_unless($user && $user->can('view-projects'), 403);

        if ($user->hasRole('Super Admin')) {
            return;
        }

        abort_unless(in_array($project->department_id, $this->allowedDepartmentIds($user), true), 403);
    }

    private function authorizeProjectAdminAction($user, Project $project): void
    {
        abort_unless($user && $user->can('edit-projects'), 403);

        if ($user->hasRole('Super Admin')) {
            return;
        }

        abort_unless(in_array($project->department_id, $this->allowedDepartmentIds($user), true), 403);
    }

    private function authorizeProjectPersonsAction($user, Project $project): void
    {
        abort_unless($user && $user->can('manage-project-persons'), 403);

        if ($user->hasRole('Super Admin')) {
            return;
        }

        abort_unless(in_array($project->department_id, $this->allowedDepartmentIds($user), true), 403);
    }

    private function allowedDepartmentIds($user): array
    {
        if ($user->hasRole('Super Admin')) {
            return Department::query()->pluck('id')->all();
        }

        $organizationIds = [];

        if ($user->person) {
            $organizationIds = $user->person
                ->affiliations()
                ->where('status', 'active')
                ->pluck('organization_id')
                ->all();
        }

        $departmentIds = Department::query()
            ->where(function ($query) use ($user, $organizationIds) {
                $query->where('admin_user_id', $user->id)
                    ->orWhereHas('projects', function ($projectQuery) use ($user) {
                        $projectQuery->where('admin_user_id', $user->id);
                    });

                if (!empty($organizationIds) && $user->can('assign-project-admins')) {
                    $query->orWhereIn('organization_id', $organizationIds);
                }
            })
            ->pluck('id')
            ->all();

        return array_values(array_unique($departmentIds));
    }
}
