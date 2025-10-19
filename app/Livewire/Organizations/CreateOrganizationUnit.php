<?php

namespace App\Livewire\Organizations;

use Livewire\Component;
use App\Models\OrganizationUnit;

class CreateOrganizationUnit extends Component
{
    public $name = '';
    public $code = '';
    public $description = '';
    public $parent_unit_id = null;
    public $is_active = true;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:organization_units,code',
            'description' => 'nullable|string',
            'parent_unit_id' => 'nullable|integer',
            'is_active' => 'boolean',
        ];
    }

    public function submit()
    {
        $this->validate();
        OrganizationUnit::create([
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'parent_unit_id' => $this->parent_unit_id,
            'is_active' => $this->is_active,
            'organisation_id' => auth()->user()->organisation_id ?? null,
        ]);
        session()->flash('success', 'Organization unit created successfully.');
        return redirect()->route('organization-units.index');
    }

    public function render()
    {
        $units = OrganizationUnit::all();
        return view('livewire.organizations.create-organization-unit', [
            'units' => $units,
        ]);
    }
}
