<?php

namespace App\Livewire\Person;

use Livewire\Component;
use App\Models\Person;

class ProfileView extends Component
{
    public $person;
    public $showModal = false;

    protected $listeners = ['show-person-profile' => 'showProfile'];

    public function showProfile($personId)
    {
        $this->person = Person::with([
            'phones',
            'emailAddresses',
            'identifiers',
            'affiliations.organisation'
        ])->find($personId);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->person = null;
    }

    public function render()
    {
        return view('livewire.person.profile-view');
    }
}
