<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Organization;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all';
    public $organizationFilter = 'all';
    public $showRolesModal = false;
    public $showDeleteModal = false;
    public $showOrganizationModal = false;

    public $userId;
    public $selectedRoles = [];
    public $selectedOrganization = null;

    public function render()
    {
        $query = User::with(['roles', 'Organization']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter !== 'all') {
            $query->whereHas('roles', function($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        if ($this->organizationFilter !== 'all') {
            $query->where('organization_id', $this->organizationFilter);
        }

        $users = $query->orderBy('name')->paginate(15);
        $roles = Role::orderBy('name')->get();
        $allRoles = Role::orderBy('name')->get();
        $organizations = Organization::orderBy('legal_name')->get();

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'roles' => $roles,
            'allRoles' => $allRoles,
            'organizations' => $organizations,
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedOrganizationFilter()
    {
        $this->resetPage();
    }

    public function openRolesModal($userId)
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->userId = $user->id;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();

        $this->showRolesModal = true;
    }

    public function updateUserRoles()
    {
        $user = User::findOrFail($this->userId);
        $roles = Role::whereIn('id', $this->selectedRoles)->get();

        $user->syncRoles($roles);

        $this->showRolesModal = false;
        $this->resetForm();

        session()->flash('message', 'User roles updated successfully!');
    }

    public function openOrganizationModal($userId)
    {
        $user = User::with('Organization')->findOrFail($userId);

        $this->userId = $user->id;
        $this->selectedOrganization = $user->organization_id;

        $this->showOrganizationModal = true;
    }

    public function updateUserOrganization()
    {
        $user = User::findOrFail($this->userId);

        $user->update([
            'organization_id' => $this->selectedOrganization
        ]);

        $this->showOrganizationModal = false;
        $this->resetForm();

        session()->flash('message', 'User organization updated successfully!');
    }

    public function openDeleteModal($userId)
    {
        $this->userId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        $user = User::findOrFail($this->userId);

        // Check if user has any critical data that should prevent deletion
        // Add any business logic checks here

        $user->delete();

        $this->showDeleteModal = false;
        session()->flash('message', 'User deleted successfully!');
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);

        // Toggle email_verified_at to simulate active/inactive status
        // You might want to add a proper 'active' field to your users table
        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
            session()->flash('message', 'User deactivated successfully!');
        } else {
            $user->update(['email_verified_at' => now()]);
            session()->flash('message', 'User activated successfully!');
        }
    }

    public function closeModals()
    {
        $this->showRolesModal = false;
        $this->showDeleteModal = false;
        $this->showOrganizationModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->userId = null;
        $this->selectedRoles = [];
        $this->selectedOrganization = null;
        $this->resetErrorBag();
    }
}
