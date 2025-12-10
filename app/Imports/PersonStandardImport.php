<?php

namespace App\Imports;

use App\Models\Person;
use App\Models\Organization;
use App\Models\Phone;
use App\Models\EmailAddress;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PersonStandardImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Get organization of the current user
        $organization = auth()->user()->organization;

        // Create user for person
        $user = User::create([
            'name' => $row['given_name'] . ' ' . $row['family_name'],
            'email' => $row['email'],
            'password' => Hash::make(Str::random(10)),
        ]);
        $user->assignRole('Person');

        // Create person
        $person = Person::create([
            'person_id' => \App\Helpers\IdGenerator::generatePersonId(),
            'global_identifier' => \App\Helpers\IdGenerator::generateGlobalIdentifier(),
            'organization_id' => $organization ? $organization->id : null,
            'user_id' => $user->id,
            'given_name' => $row['given_name'] ?? '',
            'middle_name' => $row['middle_name'] ?? '',
            'family_name' => $row['family_name'] ?? '',
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'gender' => $row['gender'] ?? null,
            'address' => $row['address'] ?? '',
            'country' => $row['country'] ?? '',
            'city' => $row['city'] ?? '',
            'district' => $row['district'] ?? '',
        ]);

        // Create phone
        if (!empty($row['phone_number'])) {
            Phone::create([
                'person_id' => $person->id,
                'number' => $row['phone_number'],
            ]);
        }

        // Create email address
        if (!empty($row['email'])) {
            EmailAddress::create([
                'person_id' => $person->id,
                'email' => $row['email'],
            ]);
        }

        return $person;
    }
}
