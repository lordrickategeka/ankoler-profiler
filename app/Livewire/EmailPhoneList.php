<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EmailAddress;
use App\Models\Phone;
use App\Models\Organization;

class EmailPhoneList extends Component
{
    public $emails;
    public $phones;
    public $organizations; // To store organization data

    public function mount()
    {
        $this->emails = EmailAddress::all();
        $this->phones = Phone::all();
        $this->organizations = Organization::select('legal_name', 'contact_email', 'contact_phone')->get();
    }

    public function render()
    {
        return view('livewire.email-phone-list', [
            'emails' => $this->emails,
            'phones' => $this->phones,
            'organizations' => $this->organizations, // Pass organizations to the view
        ]);
    }
}
