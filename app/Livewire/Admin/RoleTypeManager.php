<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RoleType;
use Spatie\Permission\Models\Permission;

class RoleTypeManager extends Component
{
    use WithPagination;

    public $search = '';
    public $activeFilter = 'all';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showPermissionsModal = false;

    public $roleTypeId;
    public $code = '';
    public $name = '';
    public $description = '';
    public $active = true;
    public $selectedPermissions = [];

    protected $rules = [
        'code' => 'required|string|max:50|unique:role_types,code',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'active' => 'boolean',
    ];

    protected $messages = [
        'code.required' => 'Role type code is required.',
        'code.unique' => 'A role type with this code already exists.',
        'name.required' => 'Role type name is required.',
    ];

    public function render()
    {
        $query = RoleType::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->activeFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->activeFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $roleTypes = $query->orderBy('name')->paginate(15);
        $permissions = Permission::orderBy('name')->get();

        return view('livewire.admin.role-type-manager', [
            'roleTypes' => $roleTypes,
            'permissions' => $permissions,
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedActiveFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($roleTypeId)
    {
        $roleType = RoleType::findOrFail($roleTypeId);

        $this->roleTypeId = $roleType->id;
        $this->code = $roleType->code;
        $this->name = $roleType->name;
        $this->description = $roleType->description ?? '';
        $this->active = $roleType->active;

        $this->showEditModal = true;
    }

    public function openPermissionsModal($roleTypeId)
    {
        $roleType = RoleType::with('permissions')->findOrFail($roleTypeId);

        $this->roleTypeId = $roleType->id;
        $this->selectedPermissions = $roleType->permissions->pluck('id')->toArray();

        $this->showPermissionsModal = true;
    }

    public function openDeleteModal($roleTypeId)
    {
        $this->roleTypeId = $roleTypeId;
        $this->showDeleteModal = true;
    }

    public function createRoleType()
    {
        $this->validate();

        RoleType::create([
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;

        session()->flash('message', 'Role type created successfully!');
    }

    public function updateRoleType()
    {
        $this->validate([
            'code' => 'required|string|max:50|unique:role_types,code,' . $this->roleTypeId,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'active' => 'boolean',
        ]);

        $roleType = RoleType::findOrFail($this->roleTypeId);
        $roleType->update([
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
        ]);

        $this->resetForm();
        $this->showEditModal = false;

        session()->flash('message', 'Role type updated successfully!');
    }

    public function updatePermissions()
    {
        $roleType = RoleType::findOrFail($this->roleTypeId);
        $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();

        $roleType->syncPermissions($permissions);

        $this->showPermissionsModal = false;
        $this->resetForm();

        session()->flash('message', 'Role type permissions updated successfully!');
    }

    public function toggleStatus($roleTypeId)
    {
        $roleType = RoleType::findOrFail($roleTypeId);
        $roleType->update(['active' => !$roleType->active]);

        $status = $roleType->active ? 'activated' : 'deactivated';
        session()->flash('message', "Role type {$status} successfully!");
    }

    public function deleteRoleType()
    {
        $roleType = RoleType::findOrFail($this->roleTypeId);

        // Check if role type has any active affiliations
        if ($roleType->hasActiveAffiliations()) {
            session()->flash('error', 'Cannot delete role type. It has active affiliations.');
            $this->showDeleteModal = false;
            return;
        }

        $roleType->delete();

        $this->showDeleteModal = false;
        session()->flash('message', 'Role type deleted successfully!');
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showPermissionsModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->roleTypeId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->active = true;
        $this->selectedPermissions = [];
        $this->resetErrorBag();
    }
}
