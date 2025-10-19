<?php

namespace App\Livewire\Person;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Person;
use App\Models\FilterConfiguration;
use App\Services\PersonFilterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PersonList extends Component
{
    use WithPagination;

    public $filters = [
        'search' => '',
        'classification' => '',
        'organisation_id' => '',
        'age_range' => '',
        'gender' => '',
        'status' => '',
        'date_range' => ['start' => '', 'end' => '']
    ];

    public $dynamicFilters = [];
    public $showAdvancedFilters = false;

    // Delete person properties
    public $showDeleteModal = false;
    public $personToDeleteId = null;

    protected $queryString = [
        'filters' => ['except' => []],
        'showAdvancedFilters' => ['except' => false]
    ];

    public function mount()
    {
        $this->loadDynamicFilters();
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    // Add updatedDynamicFilters to reset pagination when dynamic filters change
    public function updatedDynamicFilters()
    {
        $this->resetPage();
    }

    // Add a method to handle page changes more gracefully
    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    // Override the resetPage method to handle it more robustly
    public function resetPage($pageName = 'page')
    {
        $this->setPage(1, $pageName);
    }

    public function resetFilters()
    {
        // Reset all filters to their default values
        $this->filters = [
            'search' => '',
            'classification' => '',
            'organisation_id' => '',
            'age_range' => '',
            'gender' => '',
            'status' => '',
            'date_range' => ['start' => '', 'end' => '']
        ];

        // Reset dynamic filters
        $this->dynamicFilters = [];
        $this->loadDynamicFilters();
        $this->resetPage();
        $this->dispatch('filters-reset');
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function confirmDelete($personId)
    {
        try {
            // Check if user is authenticated
            $user = Auth::user();
            if (!$user) {
                session()->flash('error', 'You must be logged in to delete persons.');
                return;
            }

            // Find the person
            $person = Person::find($personId);

            if (!$person) {
                session()->flash('error', 'Person not found.');
                return;
            }

            $currentOrganization = user_current_organization();
            $canViewAllPersons = false;

            if (method_exists($user, 'hasRole')) {
                $canViewAllPersons = $user->hasRole('Super Admin');
            }

            // If not super admin, check if person belongs to user's organization
            if (!$canViewAllPersons && $currentOrganization) {
                $hasAffiliation = $person->affiliations()
                    ->where('organisation_id', $currentOrganization->id)
                    ->exists();

                if (!$hasAffiliation) {
                    session()->flash('error', 'You do not have permission to delete this person.');
                    return;
                }
            }

            // Store the person ID and show modal
            $this->personToDeleteId = $personId;
            $this->showDeleteModal = true;
        } catch (\Exception $e) {
            Log::error('Confirm delete exception: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while preparing to delete the person.');
        }
    }

    public function deletePerson()
    {
        try {
            // Check if user is authenticated
            $user = Auth::user();
            if (!$user) {
                session()->flash('error', 'You must be logged in to delete persons.');
                $this->cancelDelete();
                return;
            }

            if (!$this->personToDeleteId) {
                session()->flash('error', 'No person selected for deletion.');
                $this->cancelDelete();
                return;
            }

            // Find the person
            $person = Person::find($this->personToDeleteId);

            if (!$person) {
                session()->flash('error', 'Person not found.');
                $this->cancelDelete();
                return;
            }

            // Verify permission again before deletion
            $currentOrganization = user_current_organization();
            $canViewAllPersons = false;

            if (method_exists($user, 'hasRole')) {
                $canViewAllPersons = $user->hasRole('Super Admin');
            }

            // If not super admin, verify person belongs to user's organization
            if (!$canViewAllPersons && $currentOrganization) {
                $hasAffiliation = $person->affiliations()
                    ->where('organisation_id', $currentOrganization->id)
                    ->exists();

                if (!$hasAffiliation) {
                    session()->flash('error', 'You do not have permission to delete this person.');
                    $this->cancelDelete();
                    return;
                }
            }

            $personName = $person->full_name;

            // Use database transaction for data integrity
            DB::beginTransaction();

            try {
                // Delete domain-specific records first (they reference affiliations)
                $affiliationIds = DB::table('person_affiliations')
                    ->where('person_id', $person->id)
                    ->pluck('id');

                if ($affiliationIds->isNotEmpty()) {
                    // Delete domain records that reference affiliations
                    DB::table('staff_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('student_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('patient_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('sacco_member_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('parish_member_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                }

                // Delete related records
                DB::table('phones')->where('person_id', $person->id)->delete();
                DB::table('email_addresses')->where('person_id', $person->id)->delete();
                DB::table('person_identifiers')->where('person_id', $person->id)->delete();
                DB::table('person_affiliations')->where('person_id', $person->id)->delete();

                // Delete the person
                $person->delete();

                DB::commit();

                // Reset modal state
                $this->showDeleteModal = false;
                $this->personToDeleteId = null;

                // Show success message
                session()->flash('message', "Person '{$personName}' has been successfully deleted.");

                // Refresh the list
                $this->resetPage();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error deleting person: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Delete person exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            session()->flash('error', 'Failed to delete person: ' . $e->getMessage());

            $this->cancelDelete();
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->personToDeleteId = null;
    }

    protected function loadDynamicFilters()
    {
        $currentOrganisation = user_current_organization();

        if ($currentOrganisation) {
            $configurations = FilterConfiguration::activeForOrganisation($currentOrganisation->id)->get();

            foreach ($configurations as $config) {
                if (!isset($this->dynamicFilters[$config->field_name])) {
                    $this->dynamicFilters[$config->field_name] = '';
                }
            }
        }
    }

    public function render()
    {
        $user = Auth::user();
        $currentOrganization = user_current_organization();

        // Check permissions instead of roles
        $canViewAllPersons = false;
        $canViewOrgPersons = false;

        if ($user) {
            if (method_exists($user, 'hasRole')) {
                $canViewAllPersons = $user->hasRole('Super Admin');
                $canViewOrgPersons = $user->hasRole(['Organisation Admin', 'Department Manager', 'Data Entry Clerk']);
            }
        }

        // If user has no permissions to view persons, return empty result
        if (!$canViewAllPersons && !$canViewOrgPersons) {
            return view('livewire.person.person-list', [
                'persons' => collect(),
                'availableRoles' => [],
                'genderOptions' => [],
                'statusOptions' => [],
                'ageRanges' => [],
                'organizations' => collect(),
                'filterConfigurations' => collect(),
                'isSuperAdmin' => false,
                'currentOrganization' => null,
                'personToDelete' => null
            ]);
        }

        // Initialize filter service after permission checks
        // For Super Admins, don't pass organization to allow viewing all persons
        $filterService = new PersonFilterService($canViewAllPersons ? null : $currentOrganization);

        // Only force organization filter if user is not Super Admin
        if ($canViewOrgPersons && !$canViewAllPersons && $currentOrganization) {
            // Only set if not already set or if different from current org
            if (empty($this->filters['organisation_id']) || $this->filters['organisation_id'] != $currentOrganization->id) {
                $this->filters['organisation_id'] = $currentOrganization->id;
            }
        }

        // Ensure Super Admins don't have organization filter applied by default
        if ($canViewAllPersons) {
            // Remove organization filter for Super Admins to see all persons
            unset($this->filters['organisation_id']);
        }

        // BUG FIX #8: Handle case where Super Admin clears organization filter
        if ($canViewAllPersons && isset($this->filters['organisation_id']) && $this->filters['organisation_id'] === '') {
            // Allow empty organization filter for Super Admin
            unset($this->filters['organisation_id']);
        }

        // Merge dynamic filters with main filters
        $allFilters = array_merge($this->filters, $this->dynamicFilters);

        // BUG FIX #9: Remove empty filters to prevent issues with filter service
        $allFilters = array_filter($allFilters, function ($value) {
            if (is_array($value)) {
                // For date ranges, check if both start and end are empty
                if (isset($value['start']) && isset($value['end'])) {
                    return !empty($value['start']) || !empty($value['end']);
                }
                return !empty(array_filter($value));
            }
            return $value !== '' && $value !== null;
        });

        // Apply all filters
        try {
            $persons = $filterService->applyFilters($allFilters)->paginate(10);

            // Ensure pagination is properly set up
            $persons->withPath(request()->url());
        } catch (\Exception $e) {
            Log::error('Error applying filters: ' . $e->getMessage(), [
                'filters' => $allFilters,
                'user_id' => $user->id ?? null
            ]);

            // Fallback to basic query if filter fails
            $query = Person::query();

            if ($canViewOrgPersons && !$canViewAllPersons && $currentOrganization) {
                $query->whereHas('affiliations', function ($q) use ($currentOrganization) {
                    $q->where('organisation_id', $currentOrganization->id);
                });
            }

            $persons = $query->paginate(10);

            session()->flash('warning', 'Some filters could not be applied. Showing basic results.');
        }

        // Get filter configurations for the current organisation
        $filterConfigurations = collect();
        if ($currentOrganization) {
            $filterConfigurations = FilterConfiguration::activeForOrganisation($currentOrganization->id)->get();
        }

        // Get available options for dropdowns
        // BUG FIX #10: Make role options dynamic based on organization category
        $availableRoles = $this->getAvailableRolesForOrganization($currentOrganization);
        $genderOptions = ['male', 'female', 'other', 'prefer_not_to_say'];
        $statusOptions = ['active', 'inactive', 'suspended'];
        $ageRanges = ['18-25', '26-35', '36-45', '46-55', '56-65', '65+'];

        // Organizations for filter
        if ($canViewAllPersons) {
            $organizations = \App\Models\Organisation::select('id', 'legal_name', 'display_name')
                ->where('is_active', true)
                ->orderBy('legal_name')
                ->get();
        } else {
            $organizations = $currentOrganization ? collect([$currentOrganization]) : collect();
        }

        // Get the person to delete if needed for the modal
        $personToDelete = null;
        if ($this->personToDeleteId) {
            $personToDelete = Person::find($this->personToDeleteId);
        }

        return view('livewire.person.person-list', [
            'persons' => $persons,
            'availableRoles' => $availableRoles,
            'genderOptions' => $genderOptions,
            'statusOptions' => $statusOptions,
            'ageRanges' => $ageRanges,
            'organizations' => $organizations,
            'filterConfigurations' => $filterConfigurations,
            'isSuperAdmin' => $canViewAllPersons,
            'currentOrganization' => $currentOrganization,
            'personToDelete' => $personToDelete
        ])->layout('layouts.app', [
            'title' => 'Person - Alpha',
            'pageTitle' => 'Person Mgt'
        ]);
    }

    /**
     * Get available roles based on organization category
     * BUG FIX #10: Helper method
     */
    private function getAvailableRolesForOrganization($organization)
    {
        if (!$organization) {
            return ['STAFF', 'MEMBER', 'VOLUNTEER', 'CONSULTANT', 'VENDOR'];
        }

        return match ($organization->category) {
            'hospital' => ['STAFF', 'PATIENT', 'VOLUNTEER', 'CONSULTANT', 'VENDOR'],
            'school' => ['STAFF', 'STUDENT', 'ALUMNI', 'GUARDIAN', 'VOLUNTEER', 'CONSULTANT'],
            'sacco' => ['STAFF', 'MEMBER', 'BOARD_MEMBER', 'CONSULTANT', 'VENDOR'],
            'parish' => ['STAFF', 'PARISHIONER', 'VOLUNTEER', 'CONSULTANT'],
            default => ['STAFF', 'MEMBER', 'VOLUNTEER', 'CONSULTANT', 'VENDOR']
        };
    }
}
