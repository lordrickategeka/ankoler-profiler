<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class OrganizationSwitcher extends Component
{
    public $currentOrganizationId;
    public $currentOrganizationName;
    public $currentOrganizationLogo;
    public $availableOrganizations = [];
    public $isOpen = false;
    public $searchTerm = '';
    // For display purposes
    public $userRole = 'User';
    public $canSwitchOrganizations = false;

    protected $listeners = [
        'organizationSwitched' => '$refresh',
        'refreshOrganizations' => 'loadAvailableOrganizations'
    ];

    public function mount()
    {
        $this->loadCurrentOrganization();
        $this->loadAvailableOrganizations();
        $this->checkSwitchPermission();
    }

    /**
     * Load current organization from session
     */
    public function loadCurrentOrganization()
    {
        $this->currentOrganizationId = session('current_organization_id');
        $this->currentOrganizationName = session('current_organization_name', 'Select Organization');
        $this->currentOrganizationLogo = session('current_organization_logo');
    }

    /**
     * Load organizations user can access
     */
    public function loadAvailableOrganizations()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->availableOrganizations = [];
            return;
        }

        $organizations = $user->canAccessibleOrganizations();
        
        // Filter by search term if provided
        if (!empty($this->searchTerm)) {
            $searchLower = strtolower($this->searchTerm);
            $organizations = $organizations->filter(function($org) use ($searchLower) {
                return str_contains(strtolower($org->display_name), $searchLower) ||
                       str_contains(strtolower($org->code), $searchLower);
            });
        }

        // Format for display with additional info
        $this->availableOrganizations = $organizations->map(function($org) use ($user) {
            return [
                'id' => $org->id,
                'code' => $org->code,
                'display_name' => $org->display_name,
                'legal_name' => $org->legal_name,
                'logo_path' => $org->logo_path,
                'category' => $org->category,
                'user_role' => $user->getRoleInOrganization($org->id)?->name ?? 'Admin',
                'is_primary' => $org->id === $user->organization_id,
                'site_count' => $org->sites()->count(),
            ];
        })->values()->toArray();
    }
    
    /**
     * Check if user can switch organizations
     */
    // public function checkSwitchPermission()
    // {
    //     $user = Auth::user();
        
    //     if (!$user) {
    //         $this->canSwitchOrganizations = false;
    //         $this->userRole = 'Guest';
    //         return;
    //     }
        
    //     // Super Admin can always switch
    //     if ($user->hasRole('Super Admin')) {
    //         $this->canSwitchOrganizations = true;
    //         $this->userRole = 'Super Admin';
    //         return;
    //     }

    //     // Check if user has access to multiple organizations
    //     $accessibleCount = count($this->availableOrganizations);
    //     $this->canSwitchOrganizations = $accessibleCount > 1;
        
    //     // Get user's role in current organization
    //     $currentRole = $user->getRoleInOrganization($this->currentOrganizationId);
    //     $this->userRole = $currentRole?->name ?? 'User';
    // }

    public function checkSwitchPermission()
{
    $user = Auth::user();
    
    if (!$user) {
        $this->canSwitchOrganizations = false;
        $this->userRole = 'Guest';
        return;
    }
    
    // Super Admin can always switch
    if ($user->hasRole('Super Admin')) {
        $this->canSwitchOrganizations = true;
        $this->userRole = 'Super Admin';
        return;
    }

    // Make sure organizations are loaded before counting
    if (empty($this->availableOrganizations)) {
        $this->loadAvailableOrganizations();
    }
    
    // Check if user has access to multiple organizations
    $accessibleCount = count($this->availableOrganizations);
    $this->canSwitchOrganizations = $accessibleCount > 1;
    
    // Get user's role in current organization
    $currentRole = $user->getRoleInOrganization($this->currentOrganizationId);
    $this->userRole = $currentRole?->name ?? 'User';
}

    /**
     * Switch to a different organization
     */
    public function switchOrganization($organizationId)
    {
        $user = Auth::user();
        // Verify user has access
        if (!$user->canAccessOrganization($organizationId)) {
            session()->flash('error', 'You do not have access to this organization.');
            return;
        }
        // Get organization details
        $organization = Organization::find($organizationId);
        if (!$organization) {
            session()->flash('error', 'Organization not found.');
            return;
        }
        // Set in session
        session([
            'current_organization_id' => $organization->id,
            'current_organization_name' => $organization->display_name,
            'current_organization_code' => $organization->code,
            'current_organization_logo' => $organization->logo_path,
        ]);
        // Update component state
        $this->currentOrganizationId = $organization->id;
        $this->currentOrganizationName = $organization->display_name;
        $this->currentOrganizationLogo = $organization->logo_path;
        $this->isOpen = false;
        // Emit event to refresh other components
        $this->emit('organizationSwitched', $organization->id);
        // Flash success message
        session()->flash('message', "Switched to {$organization->display_name}");
        // Log the switch for audit
        activity()
            ->causedBy($user)
            ->withProperties([
                'organization_id' => $organization->id,
                'organization_name' => $organization->display_name,
            ])
            ->log('Organization context switched');
        // Redirect to dashboard to refresh all data
        return redirect()->route('dashboard');
    }

    /**
     * Toggle dropdown
     */
    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
        // Reset search when closing
        if (!$this->isOpen) {
            $this->searchTerm = '';
            $this->loadAvailableOrganizations();
        }
    }

    /**
     * Search organizations
     */
    public function updatedSearchTerm()
    {
        $this->loadAvailableOrganizations();
    }

    /**
     * Close dropdown
     */
    public function closeDropdown()
    {
        $this->isOpen = false;
        $this->searchTerm = '';
        $this->loadAvailableOrganizations();
    }

    public function render()
    {
        return view('livewire.organization-switcher');
    }
}
