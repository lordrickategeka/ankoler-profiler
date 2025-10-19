<?php

namespace App\Livewire\Person;

use Livewire\Component;
use App\Models\Organisation;
use App\Services\PersonExportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExportPersons extends Component
{
    // Organization context
    public $currentOrganisation;
    public $selectedOrganisationId;
    public $availableOrganisations = [];
    public $isSuperAdmin = false;

    // Export options
    public $exportFormat = 'xlsx';
    public $includeFields = [];
    public $filters = [];

    // UI state
    public $isProcessing = false;
    public $showAdvancedFilters = false;
    public $exportStats = [];
    public $availableFilterOptions = [];
    public $availableFieldOptions = [];

    protected $rules = [
        'selectedOrganisationId' => 'required_if:isSuperAdmin,true|exists:organisations,id',
        'exportFormat' => 'required|in:xlsx,csv',
        'includeFields' => 'required|array|min:1',
        'filters.role_type' => 'nullable|string',
        'filters.gender' => 'nullable|in:male,female,other,prefer_not_to_say',
        'filters.status' => 'nullable|in:active,inactive,suspended',
        'filters.age_from' => 'nullable|numeric|min:0|max:120',
        'filters.age_to' => 'nullable|numeric|min:0|max:120|gte:filters.age_from',
        'filters.city' => 'nullable|string|max:255',
        'filters.district' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'selectedOrganisationId.required_if' => 'Please select an organization.',
        'includeFields.required' => 'Please select at least one field to export.',
        'includeFields.min' => 'Please select at least one field to export.',
        'filters.age_to.gte' => 'Age To must be greater than or equal to Age From.',
    ];

    public function mount()
    {
        $this->initializeOrganizationContext();
        $this->setDefaultFields();
        $this->loadExportOptions();
        $this->loadStats();
    }

    public function updatedSelectedOrganisationId()
    {
        if ($this->selectedOrganisationId) {
            $this->currentOrganisation = Organisation::find($this->selectedOrganisationId);
            $this->loadExportOptions();
            $this->setDefaultFields();
            $this->loadStats();
        }
    }

    public function updatedFilters()
    {
        $this->loadStats();
    }

    public function exportPersons()
    {
        $this->validate();

        try {
            $this->isProcessing = true;

            $service = new PersonExportService();
            
            $organizationId = $this->isSuperAdmin ? $this->selectedOrganisationId : $this->currentOrganisation?->id;
            
            if ($this->exportFormat === 'xlsx') {
                $filePath = $service->exportToExcel($organizationId, $this->filters, $this->includeFields);
            } else {
                $filePath = $service->exportToCsv($organizationId, $this->filters, $this->includeFields);
            }

            // Log the export activity
            $orgName = $this->currentOrganisation?->legal_name ?? 'All Organizations';
            Log::info('Person export completed', [
                'user_id' => Auth::id(),
                'organization' => $orgName,
                'format' => $this->exportFormat,
                'fields' => $this->includeFields,
                'filters' => $this->filters
            ]);

            session()->flash('message', 'Export completed successfully! Download will start automatically.');

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $this->addError('export', 'Export failed: ' . $e->getMessage());
            Log::error('PersonExport: Export error - ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function clearFilters()
    {
        $this->filters = [];
        $this->loadStats();
    }

    public function selectAllFields()
    {
        $this->includeFields = array_keys($this->availableFieldOptions);
    }

    public function selectDefaultFields()
    {
        $this->setDefaultFields();
    }

    public function render()
    {
        return view('livewire.person.export-persons');
    }

    private function initializeOrganizationContext()
    {
        $user = Auth::user();

        // Check if user is Super Admin
        $this->isSuperAdmin = false;
        if ($user && $user->hasRole('Super Admin')) {
            $this->isSuperAdmin = true;
        }

        if ($this->isSuperAdmin) {
            // Load all organizations for Super Admin
            $this->availableOrganisations = Organisation::orderBy('legal_name')->get()->toArray();

            // Set current organization from session or default to first
            $currentOrgId = current_organisation_id();
            if ($currentOrgId) {
                $this->selectedOrganisationId = $currentOrgId;
                $this->currentOrganisation = Organisation::find($currentOrgId);
            } elseif (!empty($this->availableOrganisations)) {
                $this->selectedOrganisationId = $this->availableOrganisations[0]['id'];
                $this->currentOrganisation = Organisation::find($this->selectedOrganisationId);
            }
        } else {
            // Regular users use current organization context
            $this->currentOrganisation = current_organisation();
            if ($this->currentOrganisation) {
                $this->selectedOrganisationId = $this->currentOrganisation->id;
            }
        }
    }

    private function getCurrentUserOrganisation()
    {
        // Use helper function
        return current_organisation();
    }

    private function loadExportOptions()
    {
        $service = new PersonExportService();
        
        $organizationId = $this->isSuperAdmin ? $this->selectedOrganisationId : $this->currentOrganisation?->id;
        
        $this->availableFieldOptions = $service->getAvailableFields($organizationId);
        $this->availableFilterOptions = $service->getAvailableFilters($organizationId);
    }

    private function setDefaultFields()
    {
        $this->includeFields = [];
        foreach ($this->availableFieldOptions as $key => $field) {
            if ($field['default']) {
                $this->includeFields[] = $key;
            }
        }
    }

    private function loadStats()
    {
        try {
            $service = new PersonExportService();
            $organizationId = $this->isSuperAdmin ? $this->selectedOrganisationId : $this->currentOrganisation?->id;
            
            $this->exportStats = $service->getExportStats($organizationId, $this->filters);
        } catch (\Exception $e) {
            Log::error('Error loading export stats: ' . $e->getMessage());
            $this->exportStats = ['total_persons' => 0];
        }
    }
}