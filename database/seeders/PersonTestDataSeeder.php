<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\Organisation;
use App\Models\PersonAffiliation;
use App\Models\Phone;
use App\Models\EmailAddress;
use App\Models\PersonIdentifier;
use Illuminate\Support\Str;

class PersonTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have organizations
        $hospital = Organisation::firstOrCreate([
            'legal_name' => 'St. Mary Hospital'
        ], [
            'code' => 'SMH001',
            'display_name' => 'St. Mary Hospital',
            'registration_number' => 'REG-SMH-001',
            'country_of_registration' => 'UGA',
            'date_established' => '2000-01-01',
            'contact_email' => 'info@stmaryhospital.ug',
            'contact_phone' => '+256701000001',
            'category' => 'hospital',
            'address_line_1' => 'Plot 123, Kampala Road',
            'city' => 'Kampala',
            'district' => 'Kampala',
            'country' => 'UGA',
            'primary_contact_name' => 'Dr. Sarah Admin',
            'primary_contact_email' => 'admin@stmaryhospital.ug',
            'primary_contact_phone' => '+256701000001',
            'is_active' => true
        ]);

        $sacco = Organisation::firstOrCreate([
            'legal_name' => 'Unity SACCO'
        ], [
            'code' => 'USC001',
            'display_name' => 'Unity SACCO',
            'registration_number' => 'REG-USC-001',
            'country_of_registration' => 'UGA',
            'date_established' => '2015-03-15',
            'contact_email' => 'info@unitysacco.ug',
            'contact_phone' => '+256702000001',
            'category' => 'sacco',
            'address_line_1' => 'Plot 456, Ntinda Road',
            'city' => 'Kampala',
            'district' => 'Kampala',
            'country' => 'UGA',
            'primary_contact_name' => 'John Manager',
            'primary_contact_email' => 'manager@unitysacco.ug',
            'primary_contact_phone' => '+256702000001',
            'is_active' => true
        ]);

        // Create test person 1: Jane Doe (will be used for deduplication testing)
        $jane = Person::updateOrCreate([
            'person_id' => Person::generatePersonId(),
            'global_identifier' => (string) Str::uuid(),
            'given_name' => 'Jane',
            'family_name' => 'Mbabazi',
            'date_of_birth' => '1995-03-20',
            'gender' => 'female',
            'address' => 'Kampala, Uganda',
            'city' => 'Kampala',
            'district' => 'Kampala',
            'country' => 'Uganda',
            'classification' => json_encode(['STAFF']),
            'status' => 'active'
        ]);

        // Add contact info for Jane
        Phone::create([
            'person_id' => $jane->id,
            'number' => '+256700123456',
            'type' => 'mobile',
            'is_primary' => true,
            'status' => 'active'
        ]);

        EmailAddress::create([
            'person_id' => $jane->id,
            'email' => 'jane.doe@email.com',
            'type' => 'personal',
            'is_primary' => true,
            'status' => 'active'
        ]);

        PersonIdentifier::updateOrCreate(
            [
                'type' => 'national_id',
                'identifier' => 'CM950320123456XYZ',
            ],
            [
                'person_id' => $jane->id,
                'issuing_authority' => 'NIRA',
                'status' => 'active'
            ]
        );

        // Create affiliation for Jane at hospital
        PersonAffiliation::create([
            'person_id' => $jane->id,
            'organisation_id' => $hospital->id,
            'role_type' => 'STAFF',
            'role_title' => 'Senior Nurse',
            'site' => 'ICU',
            'start_date' => '2020-01-15',
            'status' => 'active'
        ]);

        // Create test person 2: John Smith
        $john = Person::create([
            'person_id' => Person::generatePersonId(),
            'global_identifier' => (string) Str::uuid(),
            'given_name' => 'John',
            'family_name' => 'Smith',
            'date_of_birth' => '1988-07-10',
            'gender' => 'male',
            'address' => 'Entebbe, Uganda',
            'city' => 'Entebbe',
            'district' => 'Wakiso',
            'country' => 'Uganda',
            'classification' => json_encode(['MEMBER']),
            'status' => 'active'
        ]);

        Phone::create([
            'person_id' => $john->id,
            'number' => '+256701234567',
            'type' => 'mobile',
            'is_primary' => true,
            'status' => 'active'
        ]);

        EmailAddress::create([
            'person_id' => $john->id,
            'email' => 'john.smith@email.com',
            'type' => 'personal',
            'is_primary' => true,
            'status' => 'active'
        ]);

        PersonIdentifier::updateOrCreate(
            [
                'type' => 'national_id',
                'identifier' => 'CM880710987654ABC',
            ],
            [
                'person_id' => $john->id,
                'issuing_authority' => 'NIRA',
                'status' => 'active'
            ]
        );

        // Create affiliation for John at SACCO
        PersonAffiliation::create([
            'person_id' => $john->id,
            'organisation_id' => $sacco->id,
            'role_type' => 'MEMBER',
            'role_title' => 'Senior Member',
            'site' => 'Kampala Branch',
            'start_date' => '2019-05-20',
            'status' => 'active'
        ]);

        // Create test person 3: Mary Johnson (similar to Jane for fuzzy matching test)
        $mary = Person::create([
            'person_id' => Person::generatePersonId(),
            'global_identifier' => (string) Str::uuid(),
            'given_name' => 'Marie',
            'family_name' => 'Doe',
            'date_of_birth' => '1995-03-20', // Same DOB as Jane
            'gender' => 'female',
            'address' => 'Jinja, Uganda',
            'city' => 'Jinja',
            'district' => 'Jinja',
            'country' => 'Uganda',
            'classification' => json_encode(['PATIENT']),
            'status' => 'active'
        ]);

        Phone::create([
            'person_id' => $mary->id,
            'number' => '+256702345678',
            'type' => 'mobile',
            'is_primary' => true,
            'status' => 'active'
        ]);

        EmailAddress::create([
            'person_id' => $mary->id,
            'email' => 'marie.doe@hospital.ug',
            'type' => 'work',
            'is_primary' => true,
            'status' => 'active'
        ]);

        PersonAffiliation::create([
            'person_id' => $mary->id,
            'organisation_id' => $hospital->id,
            'role_type' => 'PATIENT',
            'start_date' => '2023-08-15',
            'status' => 'active'
        ]);

        $this->command->info('✅ Test data created successfully!');
        $this->command->info("📋 Created persons:");
        $this->command->info("  - Jane Doe ({$jane->person_id}) - Staff at {$hospital->legal_name}");
        $this->command->info("  - John Smith ({$john->person_id}) - Member at {$sacco->legal_name}");
        $this->command->info("  - Marie Doe ({$mary->person_id}) - Patient at {$hospital->legal_name}");
        $this->command->info("");
        $this->command->info("🧪 Test scenarios:");
        $this->command->info("  1. Try registering 'Jane Doe' with phone +256700123456 → Should find exact match");
        $this->command->info("  2. Try registering 'Jane Doe' with email jane.doe@email.com → Should find exact match");
        $this->command->info("  3. Try registering 'Marie Doe' born 1995-03-20 → Should find fuzzy name match");
        $this->command->info("  4. Try registering completely new person → Should create new record");
    }
}
