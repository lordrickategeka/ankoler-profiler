<?php

namespace App\Livewire\Organizations;

use App\Models\Organisation;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public Organisation $organization;
    public $showAddAdminModal = false;

    public function mount($id)
    {
        $this->organization = Organisation::findOrFail($id);
    }

    public function hasAdmin()
    {
        return User::where('organisation_id', $this->organisation->id)
                   ->role('Organisation Admin')
                   ->exists();
    }

    public function render()
    {
        return view('livewire.organizations.show');
    }
}
