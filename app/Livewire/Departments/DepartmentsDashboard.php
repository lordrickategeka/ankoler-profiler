<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use App\Models\Organization;
use App\Models\PersonAffiliation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DepartmentsDashboard extends Component
{
    public $activeDepartmentId = null;
    public $asOfDate = null;

    public function mount(): void
    {
        $this->asOfDate = Carbon::now()->toDateString();

        // Set default department based on user role
        if (!$this->activeDepartmentId) {
            /** @var User|null $user */
            $user = Auth::user();
            $isOrgAdmin = $user && method_exists($user, 'hasRole')
                && $user->hasRole('Organization Admin') && !$user->hasRole('Super Admin');

            if ($isOrgAdmin && $user->person) {
                // For Org Admin: default to their first affiliated department
                $this->activeDepartmentId = PersonAffiliation::where('person_id', $user->person->id)
                    ->where('status', 'active')
                    ->whereNotNull('department_id')
                    ->value('department_id');
            }

            // Fallback: first department alphabetically
            if (!$this->activeDepartmentId) {
                $this->activeDepartmentId = Department::query()->orderBy('name')->value('id');
            }
        }
    }

    public function selectDepartment(int $departmentId): void
    {
        $this->activeDepartmentId = $departmentId;
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $canViewDepartments = (bool) $user
            && ((method_exists($user, 'can') && $user->can('view-departments-dashboard'))
                || (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Organization Admin'))));

        abort_unless($canViewDepartments, 403);

        $isSuperAdmin = (bool) $user && method_exists($user, 'hasRole') && $user->hasRole('Super Admin');
        $isOrgAdmin = (bool) $user && method_exists($user, 'hasRole')
            && $user->hasRole('Organization Admin') && !$user->hasRole('Super Admin');

        // For Org Admin: scope to affiliated departments only
        $affiliatedDepartmentIds = collect();
        if ($isOrgAdmin && $user->person) {
            $affiliatedDepartmentIds = PersonAffiliation::where('person_id', $user->person->id)
                ->where('status', 'active')
                ->whereNotNull('department_id')
                ->pluck('department_id')
                ->unique();
        }

        $departmentsQuery = Department::query()
            ->with(['organization:id,legal_name', 'admin:id,name', 'subCategories:id,department_id,name,is_active'])
            ->withCount(['projects', 'subCategories'])
            ->orderBy('name');

        // Org Admin only sees their affiliated departments
        if ($isOrgAdmin) {
            $departmentsQuery->whereIn('id', $affiliatedDepartmentIds);
        }

        $departments = $departmentsQuery->get();

        $ankoleDepartments = $departments->filter(function ($department) {
            $organizationName = strtolower(trim((string) ($department->organization?->legal_name ?? '')));

            return $organizationName === 'ankole diocese';
        })->values();

        $nonAnkoleDepartments = $departments->filter(function ($department) {
            $organizationName = strtolower(trim((string) ($department->organization?->legal_name ?? '')));

            return $organizationName !== 'ankole diocese';
        })->values();

        if (!$this->activeDepartmentId && $departments->isNotEmpty()) {
            $this->activeDepartmentId = (int) $departments->first()->id;
        }

        $selectedDepartment = $departments->firstWhere('id', (int) $this->activeDepartmentId);

        // If selected department is not in the available list, reset to first available
        if (!$selectedDepartment && $departments->isNotEmpty()) {
            $this->activeDepartmentId = (int) $departments->first()->id;
            $selectedDepartment = $departments->first();
        }

        // Dynamic: get sub-category names for the selected department
        $subCategoryNames = $selectedDepartment
            ? $selectedDepartment->subCategories->pluck('name')->map(fn($n) => strtolower(trim($n)))->filter()->values()
            : collect();

        $hasSubCategories = $subCategoryNames->isNotEmpty();

        // Find organizations whose category matches any sub-category of the selected department
        $departmentOrganizations = collect();

        if ($hasSubCategories) {
            $departmentOrganizations = Organization::query()
                ->select(['id', 'legal_name', 'display_name', 'category'])
                ->where('is_super', false)
                ->whereRaw('LOWER(TRIM(category)) IN (' . $subCategoryNames->map(fn() => '?')->join(',') . ')', $subCategoryNames->all())
                ->orderBy('legal_name')
                ->get();
        }

        // Count persons by department_id from person_affiliations
        // Persons are affiliated with the super org but linked to departments via department_id
        $departmentPersonsCount = 0;

        if ($selectedDepartment) {
            // Total persons in this department
            $departmentPersonsCount = PersonAffiliation::where('department_id', $this->activeDepartmentId)
                ->where('status', 'active')
                ->distinct('person_id')
                ->count('person_id');

            // Per-organization person counts: count persons whose organization_id matches each org
            // OR whose department_id matches the selected department (since persons are linked to super org)
            if ($departmentOrganizations->isNotEmpty()) {
                $orgIds = $departmentOrganizations->pluck('id')->all();

                // Count persons directly affiliated with each org
                $directCounts = PersonAffiliation::where('status', 'active')
                    ->whereIn('organization_id', $orgIds)
                    ->select('organization_id', DB::raw('COUNT(DISTINCT person_id) as persons_count'))
                    ->groupBy('organization_id')
                    ->pluck('persons_count', 'organization_id');

                // For orgs with no direct affiliations, use department-based count
                foreach ($departmentOrganizations as $org) {
                    $org->persons_count = $directCounts->get($org->id, 0);
                }

                // If no org has direct affiliations, fall back to department count for all
                if ($directCounts->sum() === 0 && $departmentPersonsCount > 0) {
                    foreach ($departmentOrganizations as $org) {
                        $org->persons_count = $departmentPersonsCount;
                    }
                }
            }
        }

        $selectedDepartmentProjects = $selectedDepartment
            ? ($hasSubCategories
                ? \App\Models\Project::query()
                    ->with([
                        'admin:id,name',
                        'departmentSubCategory:id,department_id,name',
                        'projectDepartments:id,project_id,name,is_active',
                        'department.organization:id,legal_name,display_name,category',
                    ])
                    ->whereHas('department', function ($query) use ($subCategoryNames) {
                        $query->where('id', $this->activeDepartmentId)
                            ->orWhereHas('organization', function ($orgQuery) use ($subCategoryNames) {
                                $orgQuery->whereRaw('LOWER(TRIM(category)) IN (' . $subCategoryNames->map(fn() => '?')->join(',') . ')', $subCategoryNames->all());
                            });
                    })
                    ->orderBy('name')
                    ->get()
                : $selectedDepartment->projects()
                    ->with([
                        'admin:id,name',
                        'departmentSubCategory:id,department_id,name',
                        'projectDepartments:id,project_id,name,is_active',
                        'department.organization:id,legal_name,display_name,category',
                    ])
                    ->orderBy('name')
                    ->get())
            : collect();

        // Add person counts to each project based on its department
        if ($selectedDepartmentProjects->isNotEmpty()) {
            $projectDeptIds = $selectedDepartmentProjects->pluck('department_id')->unique()->filter()->all();
            $personsPerDept = PersonAffiliation::where('status', 'active')
                ->whereIn('department_id', $projectDeptIds)
                ->select('department_id', DB::raw('COUNT(DISTINCT person_id) as persons_count'))
                ->groupBy('department_id')
                ->pluck('persons_count', 'department_id');

            foreach ($selectedDepartmentProjects as $project) {
                $project->persons_count = $personsPerDept->get($project->department_id, 0);
            }
        }

        $asOfDate = Carbon::parse($this->asOfDate ?: Carbon::now()->toDateString())->startOfDay();

        $selectedDepartmentStats = [
            'total_projects' => $selectedDepartmentProjects->count(),
            'active_projects' => $selectedDepartmentProjects->where('is_active', true)->count(),
            'inactive_projects' => $selectedDepartmentProjects->where('is_active', false)->count(),
            'ongoing_projects' => $selectedDepartmentProjects->filter(function ($project) use ($asOfDate) {
                if (!$project->starts_on) {
                    return false;
                }

                $startsOn = Carbon::parse($project->starts_on)->startOfDay();
                $endsOn = $project->ends_on ? Carbon::parse($project->ends_on)->startOfDay() : null;

                return $startsOn->lessThanOrEqualTo($asOfDate)
                    && (!$endsOn || $endsOn->greaterThanOrEqualTo($asOfDate));
            })->count(),
            'completed_projects' => $selectedDepartmentProjects->filter(function ($project) use ($asOfDate) {
                return $project->ends_on && Carbon::parse($project->ends_on)->startOfDay()->lessThan($asOfDate);
            })->count(),
            'upcoming_projects' => $selectedDepartmentProjects->filter(function ($project) use ($asOfDate) {
                return $project->starts_on && Carbon::parse($project->starts_on)->startOfDay()->greaterThan($asOfDate);
            })->count(),
            'project_departments_count' => $selectedDepartmentProjects->sum(function ($project) {
                return $project->projectDepartments->count();
            }),
            'sub_category_coverage' => $selectedDepartmentProjects
                ->pluck('departmentSubCategory.name')
                ->filter()
                ->unique()
                ->count(),
            'recent_projects' => $selectedDepartmentProjects
                ->sortByDesc('created_at')
                ->take(5)
                ->values(),
            'total_persons' => $departmentPersonsCount,
            'total_organizations' => $departmentOrganizations->count(),
            'total_org_persons' => $departmentOrganizations->sum('persons_count'),
        ];

        $selectedDepartmentStats['completion_rate'] = $selectedDepartmentStats['total_projects'] > 0
            ? (int) round(($selectedDepartmentStats['completed_projects'] / $selectedDepartmentStats['total_projects']) * 100)
            : 0;

        $summary = [
            'total_departments' => $departments->count(),
            'active_departments' => $departments->where('is_active', true)->count(),
            'departments_with_admin' => $departments->whereNotNull('admin_user_id')->count(),
            'total_projects' => $departments->sum('projects_count'),
        ];

        return view('livewire.departments.departments-dashboard', [
            'departments' => $departments,
            'summary' => $summary,
            'selectedDepartment' => $selectedDepartment,
            'selectedDepartmentProjects' => $selectedDepartmentProjects,
            'selectedDepartmentStats' => $selectedDepartmentStats,
            'hasSubCategories' => $hasSubCategories,
            'departmentOrganizations' => $departmentOrganizations,
            'isSuperAdmin' => $isSuperAdmin,
            'isOrgAdmin' => $isOrgAdmin,
            'ankoleDepartments' => $ankoleDepartments,
            'nonAnkoleDepartments' => $nonAnkoleDepartments,
        ]);
    }
}
