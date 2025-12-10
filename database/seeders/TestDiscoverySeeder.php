<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\PersonAffiliation;
use App\Models\Organization;
use App\Models\Phone;
use App\Models\EmailAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestDiscoverySeeder extends Seeder
{
    /**
     * Create test data specifically designed to test relationship discovery algorithms
     */
    public function run(): void
    {
        $this->command->info('Creating test data for relationship discovery algorithms...');

        DB::beginTransaction();

        try {
            // Test 1: Address-based discovery
            $this->createAddressBasedTestData();

            // Test 2: Contact information discovery
            $this->createContactBasedTestData();

            // Test 3: Name pattern discovery
            $this->createNamePatternTestData();

            // Test 4: Temporal pattern discovery
            $this->createTemporalPatternTestData();

            // Test 5: Cross-organizational discovery
            $this->createCrossOrgTestData();

            DB::commit();
            $this->command->info('Test discovery data created successfully!');
            $this->command->info('Run "php artisan relationships:discover --type=all" to test the algorithms');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Test data creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test 1: Create people with shared addresses for address-based discovery
     */
    private function createAddressBasedTestData(): void
    {
        $org = Organization::where('category', 'school')->first();
        if (!$org) {
            throw new \Exception('No organization found for test data');
        } {
            $this->command->line('Creating address-based test data...');

            $sharedAddress = '789 Discovery Street';
            $sharedCity = 'Kampala';
            $sharedDistrict = 'Kampala';

            // Create a family that should be discovered via address matching
            $familyMembers = [
                [
                    'given_name' => 'Michael',
                    'family_name' => 'Ssebunya',
                    'gender' => 'male',
                    'date_of_birth' => now()->subYears(45),
                ],
                [
                    'given_name' => 'Christine',
                    'family_name' => 'Ssebunya',
                    'gender' => 'female',
                    'date_of_birth' => now()->subYears(42),
                ],
                [
                    'given_name' => 'David',
                    'family_name' => 'Ssebunya',
                    'gender' => 'male',
                    'date_of_birth' => now()->subYears(17),
                ],
                [
                    'given_name' => 'Ruth',
                    'family_name' => 'Ssebunya',
                    'gender' => 'female',
                    'date_of_birth' => now()->subYears(14),
                ]
            ];

            foreach ($familyMembers as $memberData) {
                $this->createTestPerson(array_merge($memberData, [
                    'address' => $sharedAddress,
                    'city' => $sharedCity,
                    'district' => $sharedDistrict
                ]), $org);
            }

            // Create unrelated person with same address (should be discovered as dependent/emergency contact)
            $this->createTestPerson([
                'given_name' => 'Grace',
                'family_name' => 'Namukasa',
                'gender' => 'female',
                'date_of_birth' => now()->subYears(25),
                'address' => $sharedAddress,
                'city' => $sharedCity,
                'district' => $sharedDistrict
            ], $org);
        }
    }

    /**
     * Test 2: Create people with shared contact information
     */
    private function createContactBasedTestData(): void
    {
        $this->command->line('Creating contact-based test data...');

        $sharedPhone = '+256701234567';
        $sharedEmail = 'family.contact@example.com';

        // Create people who share contact information
        $org = Organization::where('category', 'school')->first();
        if (!$org) { throw new \Exception('No organization found for test data'); }
        $person1 = $this->createTestPerson([
            'given_name' => 'Andrew',
            'family_name' => 'Kiwanuka',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(35)
        ], $org);

        $person2 = $this->createTestPerson([
            'given_name' => 'Esther',
            'family_name' => 'Namusisi',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(32)
        ], $org);

        // Add shared phone number
        $this->createSharedPhone($person1, $sharedPhone);
        $this->createSharedPhone($person2, $sharedPhone);

        // Add shared email
        $this->createSharedEmail($person1, $sharedEmail);
        $this->createSharedEmail($person2, $sharedEmail);
    }

    /**
     * Test 3: Create people with name patterns that suggest relationships
     */
    private function createNamePatternTestData(): void
    {
        $org = Organization::where('category', 'school')->first();
        if (!$org) {
            throw new \Exception('No organization found for test data');
        }
        $this->command->line('Creating name pattern test data...');

        $familyName = 'Bwambale';

        // Create people with same family name but different addresses
        $familyMembers = [
            [
                'given_name' => 'John',
                'gender' => 'male',
                'date_of_birth' => now()->subYears(50),
                'address' => '123 North Street',
                'city' => 'Kasese'
            ],
            [
                'given_name' => 'Mary',
                'gender' => 'female',
                'date_of_birth' => now()->subYears(48),
                'address' => '456 South Avenue',
                'city' => 'Kasese'
            ],
            [
                'given_name' => 'Paul',
                'gender' => 'male',
                'date_of_birth' => now()->subYears(25),
                'address' => '789 East Road',
                'city' => 'Kasese'
            ],
            [
                'given_name' => 'Sarah',
                'gender' => 'female',
                'date_of_birth' => now()->subYears(22),
                'address' => '321 West Lane',
                'city' => 'Kasese'
            ]
        ];

        foreach ($familyMembers as $memberData) {
            $this->createTestPerson(array_merge($memberData, [
                'family_name' => $familyName,
                'district' => 'Kasese'
            ]), $org);
        }
    }

    /**
     * Test 4: Create temporal patterns for discovery
     */
    private function createTemporalPatternTestData(): void
    {
        $this->command->line('Creating temporal pattern test data...');

        $school = Organization::where('category', 'school')->first();
        if (!$school) {
            $this->command->warn('No school found for temporal pattern test');
            return;
        }

        // Create potential parent
        // $parent = $this->createTestPerson([
        //     'given_name' => 'Susan',
        //     'family_name' => 'Namuli',
        //     'gender' => 'female',
        //     'date_of_birth' => now()->subYears(38)
        // ]);

        // Create potential child
        $org = Organization::where('category', 'school')->first();
        if (!$org) { throw new \Exception('No organization found for test data'); }
        $parent = $this->createTestPerson([
            'given_name' => 'Susan',
            'family_name' => 'Namuli',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(38),
            'address' => '456 Temporal Road',
            'city' => 'Mbarara',
            'district' => 'Mbarara'
        ], $org);
        $child = $this->createTestPerson([
            'given_name' => 'James',
            'family_name' => 'Namuli',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(16),
            'address' => '456 Temporal Road',
            'city' => 'Mbarara',
            'district' => 'Mbarara'
        ], $org);
        PersonAffiliation::create([
            'affiliation_id' => 'AFF-TEST-' . Str::random(6),
            'person_id' => $child->id,
            'organization_id' => $school->id,
            'role_type' => 'STUDENT',
            // 'start_date' => $childStartDate,
            'start_date' => now(),
            'status' => 'active',
            'created_by' => '1'
        ]);
    }

    /**
     * Test 5: Create cross-organizational test data
     */
    private function createCrossOrgTestData(): void
    {
        $this->command->line('Creating cross-organizational test data...');

        $hospital = Organization::where('category', 'hospital')->first();
        $sacco = Organization::where('category', 'sacco')->first();
        $school = Organization::where('category', 'school')->first();

        if (!$hospital || !$sacco || !$school) {
            $this->command->warn('Need hospital, SACCO, and school for cross-org test');
            return;
        }

        // Create person with multiple roles across organizations
        $multiRolePerson = $this->createTestPerson([
            'given_name' => 'Dr. Patricia',
            'family_name' => 'Namukasa',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(40)
        ], $hospital);

        // Create multiple affiliations
        $affiliations = [
            [$hospital, 'DOCTOR'],
            [$sacco, 'MEMBER'],
            [$school, 'PARENT']
        ];

        foreach ($affiliations as [$org, $role]) {
            PersonAffiliation::create([
                'affiliation_id' => 'AFF-TEST-' . Str::random(6),
                'person_id' => $multiRolePerson->id,
                'organization_id' => $org->id,
                'role_type' => $role,
                'start_date' => now()->subDays(rand(30, 200)),
                'status' => 'active',
                'created_by' => '1'
            ]);
        }
    }

    /**
     * Helper method to create a test person
     */
    private function createTestPerson(array $attributes, Organization $organization): Person
    {
        $user = \App\Models\User::factory()->create();
        $data = array_merge([
            'person_id' => 'TEST-' . str_pad(rand(1000, 9999), 6, '0', STR_PAD_LEFT),
            'global_identifier' => Str::uuid(),
            'classification' => json_encode(['test_discovery']),
            'country' => 'Uganda',
            'status' => 'active',
            'created_by' => '1',
            'organization_id' => $organization->id,
            'user_id' => $user->id
        ], $attributes);
        return Person::create($data);
    }

    /**
     * Create shared phone number
     */
    private function createSharedPhone(Person $person, string $number): void
    {
        Phone::create([
            'phone_id' => 'PHN-TEST-' . Str::random(6),
            'person_id' => $person->id,
            'number' => $number,
            'type' => 'mobile',
            'is_primary' => false,
            'status' => 'active',
            'created_by' => '1'
        ]);
    }

    /**
     * Create shared email
     */
    private function createSharedEmail(Person $person, string $email): void
    {
        EmailAddress::create([
            'email_id' => 'EML-TEST-' . Str::random(6),
            'person_id' => $person->id,
            'email' => $email,
            'type' => 'personal',
            'is_primary' => false,
            'status' => 'active',
            'created_by' => '1'
        ]);
    }
}
