<?php

namespace App\Livewire\Person;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Person;
use App\Models\Organisation;
use App\Models\PersonAffiliation;
use App\Models\PersonIdentifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PersonSelfRegistrationComponent extends Component
{
    use WithFileUploads;

    public $currentStep = 'basic_info';
    public $form = [
        'given_name' => '',
        'middle_name' => '',
        'family_name' => '',
        'date_of_birth' => '',
        'gender' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'country' => 'Uganda',
        'district' => '',
        'city' => '',
        'role_title' => '',
        'organisation_id' => '',
    ];

    public $documents = [];
    public $documentUploads = [];
    public $availableOrganisations = [];

    public function mount()
    {
        $this->availableOrganisations = Organisation::all();
    }

    public function addDocument()
    {
        $this->documents[] = [
            'type' => '',
            'file' => null,
        ];
    }

    public function removeDocument($index)
    {
        unset($this->documents[$index]);
        $this->documents = array_values($this->documents);
    }

    public function nextStep()
    {
        $steps = ['basic_info', 'contact_info', 'document_info', 'affiliation_info'];
        $currentIndex = array_search($this->currentStep, $steps);
        if ($currentIndex < count($steps) - 1) {
            $this->currentStep = $steps[$currentIndex + 1];
        }
    }

    public function previousStep()
    {
        $steps = ['basic_info', 'contact_info', 'document_info', 'affiliation_info'];
        $currentIndex = array_search($this->currentStep, $steps);
        if ($currentIndex > 0) {
            $this->currentStep = $steps[$currentIndex - 1];
        }
    }

    public function submit()
    {
        $this->validate([
            'form.given_name' => 'required|string|max:255',
            'form.family_name' => 'required|string|max:255',
            'form.date_of_birth' => 'required|date',
            'form.gender' => ['required', Rule::in(['Male', 'Female'])],
            'form.phone' => 'required|string|max:20',
            'form.email' => 'required|email',
            'form.address' => 'required|string',
            'form.country' => 'required|string',
            'form.district' => 'required|string',
            'form.city' => 'required|string',
            'form.role_title' => 'required|string',
            'form.organisation_id' => 'required|exists:organisations,id',
            'documents.*.type' => 'required|string',
            'documents.*.file' => 'required|file|max:10240',
        ]);


        DB::transaction(function () {
            $person = Person::create([
                'person_id' => \App\Helpers\IdGenerator::generatePersonId(),
                'global_identifier' => \App\Helpers\IdGenerator::generateGlobalIdentifier(),
                'given_name' => $this->form['given_name'],
                'middle_name' => $this->form['middle_name'],
                'family_name' => $this->form['family_name'],
                'date_of_birth' => $this->form['date_of_birth'],
                'gender' => $this->form['gender'],
                'address' => $this->form['address'],
                'country' => $this->form['country'],
                'district' => $this->form['district'],
                'city' => $this->form['city'],
            ]);

            $this->createContactInformation($person);

            PersonAffiliation::create([
                'person_id' => $person->id,
                'organisation_id' => $this->form['organisation_id'],

                // 'site' => $this->form['site'] ?: null,
                // 'role_type' => $this->form['role_type'] ?: null,
                'role_title' => $this->form['role_title'] ?: null,
                // 'start_date' => $this->form['start_date'] ?: null,
                'status' => 'active',
                'created_by' => null,
            ]);

            foreach ($this->documents as $doc) {
                $path = $doc['file']->store('documents');
                PersonIdentifier::create([
                    'person_id' => $person->id,
                    'type' => $doc['type'],
                    'document_path' => $path,
                ]);
            }
        });
    }

    private function createContactInformation(Person $person)
    {
        // Create phone
        if (!empty($this->form['phone'])) {
            \App\Models\Phone::create([
                'person_id' => $person->id,
                'number' => $this->form['phone'],
                'type' => 'mobile',
                'is_primary' => true,
                'created_by' => null, // No auth for self-registration
            ]);
        }

        // Create email
        if (!empty($this->form['email'])) {
            \App\Models\EmailAddress::create([
                'person_id' => $person->id,
                'email' => $this->form['email'],
                'type' => 'personal',
                'is_primary' => true,
                'created_by' => null, // No auth for self-registration
            ]);
        }

        session()->flash('success', 'Registration successful!');
        return redirect()->route('persons.all');
    }

    public function render()
    {
        return view('livewire.person.person-self-registration');
    }
}
