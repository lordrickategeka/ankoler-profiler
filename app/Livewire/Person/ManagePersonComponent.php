<?php

namespace App\Livewire\Person;

use Livewire\Component;
use App\Services\PersonDeduplicationService;
use App\Models\Person;

class ManagePersonComponent extends Component
{

    public $form = [
        'given_name' => '',
        'family_name' => '',
        'date_of_birth' => '',
        'phone' => '',
        'email' => '',
    ];

    public $potentialDuplicates = [];
    public $showDuplicateWarning = false;
    public $currentStep = 1;

    protected $deduplicationService;

    public function boot(PersonDeduplicationService $service)
    {
        $this->deduplicationService = $service;
    }

    public function checkForDuplicates()
    {
        $duplicates = $this->deduplicationService->findPotentialDuplicates($this->form);

        if (!empty($duplicates) && $duplicates[0]['similarity'] > 70) {
            $this->potentialDuplicates = $duplicates;
            $this->showDuplicateWarning = true;
            return false;
        }

        return true;
    }

    public function submit()
    {
        $this->validate([
            'form.given_name' => 'required|string|max:255',
            'form.family_name' => 'required|string|max:255',
            'form.date_of_birth' => 'nullable|date',
        ]);

        // Check for duplicates before final save
        if (!$this->checkForDuplicates()) {
            return; // Show duplicate warning
        }

        // Create person
        $result = $this->deduplicationService->createWithDuplicateCheck($this->form);

        if ($result['status'] === 'created') {
            session()->flash('message', 'Person created successfully!');
            $this->emit('personCreated', $result['person']->id);
            $this->reset();
        }
    }

    public function linkToExisting($existingPersonId)
    {
        $existingPerson = Person::findOrFail($existingPersonId);

        // Create new person record but link to existing global_identifier
        $person = Person::create(array_merge($this->form, [
            'global_identifier' => $existingPerson->global_identifier
        ]));

        session()->flash('message', 'Person created and linked to existing record!');
        $this->emit('personCreated', $person->id);
        $this->reset();
    }

    public function createAsNew()
    {
        // User confirmed it's not a duplicate
        $this->showDuplicateWarning = false;
        $this->potentialDuplicates = [];

        $person = Person::create($this->form);

        session()->flash('message', 'Person created successfully!');
        $this->emit('personCreated', $person->id);
        $this->reset();
    }
    public function render()
    {
        return view('livewire.person.manage-person-component');
    }
}
