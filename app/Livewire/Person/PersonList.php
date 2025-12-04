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
use Illuminate\Support\Facades\Cache;

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

        // Edit person properties
        public $editPersonId = null;
        public $editPersonData = [];
        // View person properties
        public $viewPersonId = null;
        public $viewPersonData = null;

    protected $queryString = [
        'filters' => ['except' => []],
        'showAdvancedFilters' => ['except' => false]
    ];

    public function mount()
    {
        $this->loadDynamicFilters();
    }

    // Remove the problematic Cache::remember call and fix the updatedFilters method
    public function updatedFilters()
    {
        $this->clearPersonListCache();
        $this->resetPage();
    }

    public function updatedDynamicFilters()
    {
        $this->clearPersonListCache();
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filters = [
            'search' => '',
            'classification' => '',
            'organisation_id' => '',
            'age_range' => '',
            'gender' => '',
            'status' => '',
            'date_range' => ['start' => '', 'end' => '']
        ];

        $this->dynamicFilters = [];
        $this->loadDynamicFilters();
        $this->clearPersonListCache();
        $this->resetPage();
        $this->dispatch('filters-reset');
    }

    private function clearPersonListCache()
    {
        // Clear specific cache patterns instead of Cache::flush()
        $user = Auth::user();
        $currentOrganization = user_current_organization();

        $patterns = [
            'person_list_' . md5(serialize([
                $this->filters,
                $this->dynamicFilters,
                $currentOrganization?->id ?? 'all',
                ($user && method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) ? 'all' : 'org'
            ])),
            'person_list_additional_' . ($currentOrganization?->id ?? 'all')
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function confirmDelete($personId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                session()->flash('error', 'You must be logged in to delete persons.');
                return;
            }

            $person = Person::find($personId);
            if (!$person) {
                session()->flash('error', 'Person not found.');
                return;
            }

            $currentOrganization = user_current_organization();
            $canViewAllPersons = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');

            if (!$canViewAllPersons && $currentOrganization) {
                $hasAffiliation = $person->affiliations()
                    ->where('organisation_id', $currentOrganization->id)
                    ->exists();

                if (!$hasAffiliation) {
                    session()->flash('error', 'You do not have permission to delete this person.');
                    return;
                }
            }

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
            $user = Auth::user();
            if (!$user || !$this->personToDeleteId) {
                session()->flash('error', 'Invalid delete request.');
                $this->cancelDelete();
                return;
            }

            $person = Person::find($this->personToDeleteId);
            if (!$person) {
                session()->flash('error', 'Person not found.');
                $this->cancelDelete();
                return;
            }

            $currentOrganization = user_current_organization();
            $canViewAllPersons = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');

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

            DB::beginTransaction();
            try {
                $affiliationIds = DB::table('person_affiliations')
                    ->where('person_id', $person->id)
                    ->pluck('id');

                if ($affiliationIds->isNotEmpty()) {
                    DB::table('staff_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('student_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('patient_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('sacco_member_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                    DB::table('parish_member_records')->whereIn('affiliation_id', $affiliationIds)->delete();
                }

                DB::table('phones')->where('person_id', $person->id)->delete();
                DB::table('email_addresses')->where('person_id', $person->id)->delete();
                DB::table('person_identifiers')->where('person_id', $person->id)->delete();
                DB::table('person_affiliations')->where('person_id', $person->id)->delete();

                $person->delete();
                DB::commit();

                $this->showDeleteModal = false;
                $this->personToDeleteId = null;

                session()->flash('message', "Person '{$personName}' has been successfully deleted.");
                $this->clearPersonListCache();
                $this->resetPage();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Delete person exception: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete person: ' . $e->getMessage());
            $this->cancelDelete();
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->personToDeleteId = null;
    }

        /**
         * Load a person's data for editing
         */
        public function editPerson($id)
        {
            $person = Person::findOrFail($id);
            $this->editPersonId = $person->id;
            $this->editPersonData = $person->toArray();
        }

            /**
             * Load a person's data for viewing
             */
            public function viewPerson($id)
            {
                $person = Person::find($id);
                if ($person) {
                    $this->viewPersonId = $person->id;
                    $this->viewPersonData = $person;
                } else {
                    $this->viewPersonId = null;
                    $this->viewPersonData = null;
                    session()->flash('error', 'Person not found.');
                }
            }

        /**
         * Update the person's data
         */
        public function updatePerson()
        {
            if (!$this->editPersonId) {
                return;
            }
            $person = Person::findOrFail($this->editPersonId);
            $person->fill($this->editPersonData);
            $person->save();
            // Optionally reset edit state
            $this->editPersonId = null;
            $this->editPersonData = [];
            session()->flash('message', 'Person updated successfully.');
            // Optionally refresh list or emit event
            $this->clearPersonListCache();
            $this->resetPage();
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

        $canViewAllPersons = $user && method_exists($user, 'hasRole') && $user->hasRole('Super Admin');
        $canViewOrgPersons = $user && method_exists($user, 'hasRole') &&
            (is_array(['Organisation Admin', 'Department Manager', 'Data Entry Clerk'])
                ? $user->hasRole(['Organisation Admin', 'Department Manager', 'Data Entry Clerk'])
                : false);

        if (!($user && method_exists($user, 'hasRole')) || (!$canViewAllPersons && !$canViewOrgPersons)) {
            return $this->renderEmptyState();
        }

        try {
            // Initialize filter service
            $filterService = new PersonFilterService($canViewAllPersons ? null : $currentOrganization);

            // Handle organization filtering for non-super admins
            if ($canViewOrgPersons && !$canViewAllPersons && $currentOrganization) {
                if (empty($this->filters['organisation_id']) || $this->filters['organisation_id'] != $currentOrganization->id) {
                    $this->filters['organisation_id'] = $currentOrganization->id;
                }
            }

            // Allow Super Admins to clear organization filter
            if ($canViewAllPersons && isset($this->filters['organisation_id']) && $this->filters['organisation_id'] === '') {
                unset($this->filters['organisation_id']);
            }

            // Merge and clean filters
            $allFilters = array_merge($this->filters, $this->dynamicFilters);
            $allFilters = array_filter($allFilters, function ($value) {
                if (is_array($value)) {
                    if (isset($value['start']) && isset($value['end'])) {
                        return !empty($value['start']) || !empty($value['end']);
                    }
                    return !empty(array_filter($value));
                }
                return $value !== '' && $value !== null;
            });

            // Apply filters and get paginated results
            $persons = $filterService->applyFilters($allFilters)->paginate(10);
            $persons->withPath(request()->url());

        } catch (\Exception $e) {
            Log::error('Error applying filters: ' . $e->getMessage(), [
                'filters' => $allFilters ?? [],
                'user_id' => $user->id ?? null
            ]);

            // Fallback query
            $query = Person::query();
            if ($canViewOrgPersons && !$canViewAllPersons && $currentOrganization) {
                $query->whereHas('affiliations', function ($q) use ($currentOrganization) {
                    $q->where('organisation_id', $currentOrganization->id);
                });
            }
            $persons = $query->paginate(10);
            session()->flash('warning', 'Some filters could not be applied. Showing basic results.');
        }

        // Get additional data
        $additionalData = $this->getAdditionalData($currentOrganization, $canViewAllPersons);

        return view('livewire.person.person-list', array_merge([
            'persons' => $persons,
            'isSuperAdmin' => $canViewAllPersons,
            'currentOrganization' => $currentOrganization,
            'personToDelete' => $this->personToDeleteId ? Person::find($this->personToDeleteId) : null
        ], $additionalData));
    }

    private function getAdditionalData($currentOrganization, $canViewAllPersons)
    {
        $cacheKey = 'person_list_additional_' . ($currentOrganization?->id ?? 'all');

        return Cache::remember($cacheKey, 1800, function() use ($currentOrganization, $canViewAllPersons) {
            return [
                'availableRoles' => $this->getAvailableRolesForOrganization($currentOrganization),
                'genderOptions' => ['male', 'female', 'other', 'prefer_not_to_say'],
                'statusOptions' => ['active', 'inactive', 'suspended'],
                'ageRanges' => ['18-25', '26-35', '36-45', '46-55', '56-65', '65+'],
                'organizations' => $canViewAllPersons ?
                    \App\Models\Organisation::select('id', 'legal_name', 'display_name')
                        ->where('is_active', true)
                        ->orderBy('legal_name')
                        ->get() :
                    ($currentOrganization ? collect([$currentOrganization]) : collect()),
                'filterConfigurations' => $currentOrganization ?
                    FilterConfiguration::activeForOrganisation($currentOrganization->id)->get() :
                    collect()
            ];
        });
    }

    private function renderEmptyState()
    {
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
