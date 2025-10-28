<?php

namespace App\Livewire\Organizations;

use Livewire\Component;
use App\Models\OrganizationUnit;



class CreateOrganizationUnit extends Component
{
    // Stepper
    public $currentStep = 1;
    public $totalSteps = 9;

    // Step 1: Basic Info
    public $name = '';
    public $code = '';
    public $description = '';
    public $parent_unit_id = null;
    public $organisation_id = null;
    public $unit_type = '';
    public $department = '';
    public $community = '';
    public $ministry_committee = '';
    public $administrative_office = '';

    // Step 2: Leadership & Governance
    public $unit_head = null;
    public $assistant_leader = null;
    public $leadership_committee = [];
    public $appointment_dates = '';
    public $reporting_line = '';

    // Step 3: Purpose & Mission
    public $mission = '';
    public $objectives = '';
    public $activities = '';
    public $target_audience = '';

    // Step 4: Contact Information
    public $official_email = '';
    public $phone_contact = '';
    public $physical_location = '';
    public $website = '';
    public $social_links = '';

    // Step 5: Operational Details
    public $unit_category = '';
    public $operational_status = 'active';
    public $date_established = '';
    public $faith_based = false;
    public $socio_economic = false;
    public $support_services = false;

    // Step 6: Membership Metadata
    public $membership_type = '';
    public $membership_eligibility = '';
    public $membership_capacity = '';
    public $join_requests_enabled = false;

    // Step 7: Events & Programs Metadata
    public $recurring_programs = '';
    public $event_schedule = '';
    public $promotion_permissions = '';
    public $resource_access_requirements = '';

    // Step 8: Showcase & Marketplace Support
    public $showcase_permissions = '';
    public $product_categories_allowed = '';
    public $approval_workflow = '';
    public $commission_structure = '';

    // Step 9: Roles & Permissions for Unit Users
    public $unit_roles = [];

    // Misc
    public $is_active = true;
    public $orgOptions = [];

    public function mount()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && method_exists($user, 'can') && $user->can('assign-organization-unit')) {
            $this->orgOptions = method_exists($user, 'accessibleOrganizations') ? $user->accessibleOrganizations() : [];
        } else {
            $this->organisation_id = $user && property_exists($user, 'organisation_id') ? $user->organisation_id : null;
        }
    }

    public function goToStep($step)
    {
        if ($step > 0 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
        }
    }

    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function submit()
    {
        $user = auth()->user();
        if (!$user || !$user->can('create-units')) {
            abort(403, 'You do not have permission to create organization units.');
        }
        // TODO: Add validation for all steps/fields
        // TODO: Save all fields to OrganizationUnit and related tables as needed
        session()->flash('success', 'Organization unit created successfully.');
        return redirect()->route('organization-units.index');
    }

    public function render()
    {
        $units = OrganizationUnit::all();
        return view('livewire.organizations.create-organization-unit', [
            'units' => $units,
            'orgOptions' => $this->orgOptions,
            'organisation_id' => $this->organisation_id,
            'currentStep' => $this->currentStep,
            'totalSteps' => $this->totalSteps,
        ]);
    }
}
