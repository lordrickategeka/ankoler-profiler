<?php

namespace App\Livewire\Person;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\OrganizationUnit;

class OrganizationUnitsList extends Component
{
    public $units;

    public function mount()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;
        $this->units = OrganizationUnit::where('organization_id', $orgId)->get();
    }

    public function applyToJoin($unitId)
    {
        // TODO: Implement logic to apply to join the Organization unit
        // Example: dispatch event, create application record, etc.
        session()->flash('message', 'Application to join unit submitted!');
    }

    public function render()
    {
        return view('livewire.person.Organization-units-list', [
            'units' => $this->units,
        ]);
    }
}
