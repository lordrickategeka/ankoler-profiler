<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    /**
     * Display the specified person.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $person = Person::with(['phones', 'emailAddresses', 'affiliations.Organization'])->findOrFail($id);

        return view('livewire.person.show', compact('person'));
    }
}
