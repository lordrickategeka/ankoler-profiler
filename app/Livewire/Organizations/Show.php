<?php

namespace App\Livewire\Organizations;

use App\Models\Organization;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public Organization $organization;
    public $showAddAdminModal = false;

    public function mount($id)
    {
        $this->organization = Organization::findOrFail($id);
    }

    public function hasAdmin()
    {
        return User::where('organization_id', $this->Organization->id)
                   ->role('Organization Admin')
                   ->exists();
    }

    public function render()
    {
        return view('livewire.organizations.show');
    }
}
