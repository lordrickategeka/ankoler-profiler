<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\Organization;
use App\Models\Phone;
use App\Models\EmailAddress;
use App\Models\PersonIdentifier;
use App\Models\PersonAffiliation;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating persons with affiliations across all organizations...');

        // Get all existing organizations
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run organization seeders first.');
            return;
        }

        $this->command->info("Found {$organizations->count()} organizations:");
        foreach ($organizations as $org) {
            $this->command->line("- {$org->legal_name} ({$org->category})");
        }

        $totalPersons = 0;

        foreach ($organizations as $organization) {
            $this->command->info("\nCreating persons for {$organization->legal_name} ({$organization->category})...");

            // Create different numbers of persons based on organization type
            $personCount = match($organization->category) {
                'hospital' => 8,  // Mix of patients, doctors, nurses, staff
                'school' => 10,   // Mix of students, teachers, staff
                'sacco' => 6,     // Members and staff
                'parish' => 5,    // Members and clergy
                'corporate' => 7, // Employees and managers
                default => 5
            };

            for ($i = 0; $i < $personCount; $i++) {
                // Create person with organization-specific traits
                $person = $this->createPersonForOrganization($organization);

                // Create contact information (80% chance of phone, 60% chance of email)
                if (rand(1, 10) <= 8) {
                    Phone::factory()->create(['person_id' => $person->id]);

                    // 30% chance of secondary phone
                    if (rand(1, 10) <= 3) {
                        Phone::factory()->secondary()->create(['person_id' => $person->id]);
                    }
                }

                if (rand(1, 10) <= 6) {
                    $emailType = $organization->category === 'corporate' ? 'work' : 'personal';
                    EmailAddress::factory()->create([
                        'person_id' => $person->id,
                        'type' => $emailType
                    ]);
                }

                // Create identifiers (70% chance of national ID, 20% passport, 15% others)
                if (rand(1, 10) <= 7) {
                    PersonIdentifier::factory()->create(['person_id' => $person->id]);
                }

                if (rand(1, 10) <= 2) {
                    PersonIdentifier::factory()->passport()->create(['person_id' => $person->id]);
                }

                if ($organization->category === 'hospital' && rand(1, 10) <= 3) {
                    PersonIdentifier::factory()->professionalLicense()->create(['person_id' => $person->id]);
                }

                // Create affiliation
                $affiliation = $this->createAffiliationForOrganization($person, $organization);

                $totalPersons++;

                $this->command->line("  Created: {$person->given_name} {$person->family_name} - {$affiliation->role_title}");
            }
        }

        $this->command->info("\n✅ Successfully created {$totalPersons} persons across all organizations!");
        $this->command->info("📊 Summary:");
        $this->command->line("- Persons: {$totalPersons}");
        $this->command->line("- Phone numbers: " . Phone::count());
        $this->command->line("- Email addresses: " . EmailAddress::count());
        $this->command->line("- Identifiers: " . PersonIdentifier::count());
        $this->command->line("- Affiliations: " . PersonAffiliation::count());
    }

    /**
     * Create a person tailored for specific organization type
     */
    private function createPersonForOrganization(Organization $organization): Person
    {
        // Create a user for the person first
        $user = \App\Models\User::factory()->create();

        // Create the person with both organization_id and user_id
        return match($organization->category) {
            'hospital' => Person::factory()->forHospital()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ]),
            'school' => Person::factory()->forSchool()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ]),
            'sacco' => Person::factory()->forSacco()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ]),
            'parish' => Person::factory()->forParish()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ]),
            'corporate' => Person::factory()->forCorporate()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ]),
            default => Person::factory()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ])
        };
    }

    /**
     * Create an affiliation tailored for specific organization
     */
    private function createAffiliationForOrganization(Person $person, Organization $organization): PersonAffiliation
    {
        return match($organization->category) {
            'hospital' => PersonAffiliation::factory()->forHospital($organization)->create(['person_id' => $person->id]),
            'school' => PersonAffiliation::factory()->forSchool($organization)->create(['person_id' => $person->id]),
            'sacco' => PersonAffiliation::factory()->forSacco($organization)->create(['person_id' => $person->id]),
            'parish' => PersonAffiliation::factory()->forParish($organization)->create(['person_id' => $person->id]),
            'corporate' => PersonAffiliation::factory()->forCorporate($organization)->create(['person_id' => $person->id]),
            default => PersonAffiliation::factory()->create([
                'person_id' => $person->id,
                'organization_id' => $organization->id
            ])
        };
    }
}
