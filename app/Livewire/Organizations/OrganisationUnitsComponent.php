<?php

namespace App\Livewire\Organizations;

use Livewire\Component;
use App\Models\OrganizationUnit;

class OrganisationUnitsComponent extends Component
{
    public $organizationUnits;

    public $showEditForm = false;
    public $editForm = [
        'id' => null,
        'name' => '',
        'code' => '',
        'description' => '',
        'parent_unit_id' => null,
        'is_active' => true,
    ];

    public $showCreateForm = false;
    public $createForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'parent_unit_id' => null,
        'is_active' => true,
    ];

    public function mount()
    {
        // For super admin, show all units
        $this->organizationUnits = OrganizationUnit::all();
    }

    public function render()
    {
        return view('livewire.organizations.organisation-units-component', [
            'organizationUnits' => $this->organizationUnits,
        ]);
    }

    public function editUnit($id)
    {
        $unit = OrganizationUnit::find($id);
        if ($unit) {
            $this->editForm = [
                'id' => $unit->id,
                'name' => $unit->name,
                'code' => $unit->code,
                'description' => $unit->description,
                'parent_unit_id' => $unit->parent_unit_id,
                'is_active' => $unit->is_active,
            ];
            $this->showEditForm = true;
        }
    }

    public function updateUnit()
    {
        $unit = OrganizationUnit::find($this->editForm['id']);
        if ($unit) {
            $unit->update([
                'name' => $this->editForm['name'],
                'code' => $this->editForm['code'],
                'description' => $this->editForm['description'],
                'parent_unit_id' => $this->editForm['parent_unit_id'],
                'is_active' => $this->editForm['is_active'],
            ]);
            $this->showEditForm = false;
            session()->flash('success', 'Organization unit updated successfully.');
            $this->organizationUnits = OrganizationUnit::all();
        }
    }

    public function deleteUnit($id)
    {
        $unit = \App\Models\OrganizationUnit::find($id);
        if ($unit) {
            $unit->delete();
            session()->flash('success', 'Organization unit deleted successfully.');
            $this->organizationUnits = \App\Models\OrganizationUnit::all();
        }
    }

    public function showCreateForm()
    {
        $this->resetCreateForm();
        $this->showCreateForm = true;
    }

    public function resetCreateForm()
    {
        $this->createForm = [
            'name' => '',
            'code' => '',
            'description' => '',
            'parent_unit_id' => null,
            'is_active' => true,
        ];
    }

    public function createUnit()
    {
        $this->validate([
            'createForm.name' => 'required|string|max:255',
            'createForm.code' => 'required|string|max:50|unique:organization_units,code',
            'createForm.description' => 'nullable|string',
            'createForm.parent_unit_id' => 'nullable|integer',
            'createForm.is_active' => 'boolean',
        ]);

        \App\Models\OrganizationUnit::create([
            'name' => $this->createForm['name'],
            'code' => $this->createForm['code'],
            'description' => $this->createForm['description'],
            'parent_unit_id' => $this->createForm['parent_unit_id'],
            'is_active' => $this->createForm['is_active'],
            'organisation_id' => auth()->user()->organisation_id ?? null,
        ]);

        $this->showCreateForm = false;
        $this->resetCreateForm();
        $this->organizationUnits = \App\Models\OrganizationUnit::all();
        session()->flash('success', 'Organization unit created successfully.');
    }
}
