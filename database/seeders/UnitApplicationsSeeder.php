<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonAffiliation;
use App\Models\Person;
use App\Models\OrganizationUnit;
use Illuminate\Support\Arr;

class UnitApplicationsSeeder extends Seeder
{
    public function run()
    {
        // Get some units and persons
        $units = OrganizationUnit::inRandomOrder()->take(5)->get();
        $persons = Person::inRandomOrder()->take(10)->get();

        foreach ($units as $unit) {
            foreach ($persons->random(3) as $person) {
                $roleType = Arr::random(['MEMBER', 'APPLICANT']);
                $exists = PersonAffiliation::where('person_id', $person->id)
                    ->where('organization_id', $unit->organization_id)
                    ->where('role_type', $roleType)
                    ->exists();
                if (!$exists) {
                    PersonAffiliation::create([
                        'person_id' => $person->id,
                        'organization_id' => $unit->organization_id,
                        'domain_record_type' => 'unit',
                        'domain_record_id' => $unit->id,
                        'status' => 'inactive',
                        'role_type' => $roleType,
                        'created_by' => 1,
                        'start_date' => now(),
                    ]);
                }
            }
        }
    }
}
