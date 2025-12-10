<?php

namespace App\Livewire\Person;

use Livewire\Component;
use App\Models\Person;
use Illuminate\Support\Facades\Auth;

class ProfileView extends Component
{
    public $person;

    public function mount($person = null)
    {
        // If the route parameter is 'me' or null, use the current authenticated user
        if ($person === null || $person === 'me') {
            $user = Auth::user();
            if (method_exists($user, 'person')) {
                $personModel = $user->person;
            } else {
                $personModel = Person::where('user_id', $user->id)->first();
            }
            if (!$personModel) {
                abort(404, 'Person record not found for current user.');
            }
        } else {
            $personModel = Person::find($person);
            if (!$personModel) {
                abort(404, 'Person record not found for id: ' . $person);
            }
        }
        $this->person = $personModel->load([
            'phones',
            'emailAddresses',
            'identifiers',
            'affiliations.Organization'
        ]);
    }

    public function render()
    {
        return view('livewire.person.person-profile-view');
    }
}
