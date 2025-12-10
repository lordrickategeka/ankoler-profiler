<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\PersonAffiliation;
use App\Models\PersonRelationship;
use App\Models\CrossOrgRelationship;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class DioceseSpecificSeeder extends Seeder
{
    /**
     * Seed diocese-specific scenarios
     */
    public function run(): void
    {
        $this->command->info('Creating diocese-specific relationship scenarios...');

        DB::beginTransaction();

        try {
            // Scenario 1: Parent with child at school + SACCO membership
            $this->createParentStudentSaccoScenario();

            // Scenario 2: Hospital staff who is also a patient elsewhere
            $this->createStaffPatientScenario();

            // Scenario 3: Teacher with multiple children in different schools
            $this->createMultiSchoolFamilyScenario();

            // Scenario 4: SACCO member who is also parish leader
            $this->createSaccoParishLeaderScenario();

            // Scenario 5: Hospital staff family with various connections
            $this->createHospitalStaffFamilyScenario();

            // Scenario 6: Complex family with business interests
            $this->createBusinessFamilyScenario();

            DB::commit();
            $this->command->info('Diocese-specific scenarios created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Diocese seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Scenario 1: Parent with child at school + SACCO membership
     */
    private function createParentStudentSaccoScenario(): void
    {
        $this->command->line('Creating parent-student-SACCO scenario...');

        // Get organizations
        $school = Organization::where('category', 'school')->first();
        $sacco = Organization::where('category', 'sacco')->first();
        $parish = Organization::where('category', 'parish')->first();

        if (!$school || !$sacco) {
            $this->command->warn('Required organizations not found for parent-student-SACCO scenario');
            return;
        }

        // Create parent
        $parent = $this->createPerson([
            'given_name' => 'Margaret',
            'family_name' => 'Nakamatte',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(35),
            'address' => '156 Education Road',
            'city' => 'Kampala',
            'district' => 'Kampala'
        ], $school);

        // Create child
        $child = $this->createPerson([
            'given_name' => 'Joshua',
            'family_name' => 'Nakamatte',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(12),
            'address' => '156 Education Road',
            'city' => 'Kampala',
            'district' => 'Kampala'
        ], $school);

        // Create parent-child relationship
        PersonRelationship::createRelationship($parent->id, $child->id, 'parent_child', [
            'discovery_method' => 'address_match',
            'confidence_score' => 0.98,
            'verification_status' => 'verified',
            'verified_at' => now(),
            'metadata' => ['scenario' => 'parent_student_sacco']
        ]);

        // Create affiliations
        $parentSchoolAffiliation = $this->createAffiliation($parent, $school, 'PARENT');
        $childSchoolAffiliation = $this->createAffiliation($child, $school, 'STUDENT');
        $parentSaccoAffiliation = $this->createAffiliation($parent, $sacco, 'MEMBER');

        if ($parish) {
            $parentParishAffiliation = $this->createAffiliation($parent, $parish, 'MEMBER');
            $childParishAffiliation = $this->createAffiliation($child, $parish, 'MEMBER');
        }

        // Create cross-org relationships
        CrossOrgRelationship::createCrossOrgRelationship(
            $parent->id,
            $parentSchoolAffiliation->id,
            $parentSaccoAffiliation->id,
            [
                'relationship_strength' => 'strong',
                'impact_score' => 0.85,
                'verified' => true,
                'metadata' => ['scenario' => 'parent_multiple_roles']
            ]
        );
    }

    /**
     * Scenario 2: Hospital staff who is also a patient elsewhere
     */
    private function createStaffPatientScenario(): void
    {
        $this->command->line('Creating staff-patient scenario...');

        $hospitals = Organization::where('category', 'hospital')->take(2)->get();
        if ($hospitals->count() < 2) {
            $this->command->warn('Need at least 2 hospitals for staff-patient scenario');
            return;
        }

        $nurse = $this->createPerson([
            'given_name' => 'Sarah',
            'family_name' => 'Tumwine',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(28),
            'address' => '45 Medical Avenue',
            'city' => 'Kampala',
            'district' => 'Kampala'
        ], $hospitals[0]);

        // Works as nurse at first hospital
        $staffAffiliation = $this->createAffiliation($nurse, $hospitals[0], 'NURSE');

        // Patient at second hospital
        $patientAffiliation = $this->createAffiliation($nurse, $hospitals[1], 'PATIENT');

        // Create cross-org relationship
        CrossOrgRelationship::createCrossOrgRelationship(
            $nurse->id,
            $staffAffiliation->id,
            $patientAffiliation->id,
            [
                'relationship_strength' => 'strong',
                'impact_score' => 0.90,
                'verified' => true,
                'metadata' => ['scenario' => 'healthcare_professional_patient']
            ]
        );
    }

    /**
     * Scenario 3: Teacher with multiple children in different schools
     */
    private function createMultiSchoolFamilyScenario(): void
    {
        $this->command->line('Creating multi-school family scenario...');

        $schools = Organization::where('category', 'school')->take(2)->get();
        if ($schools->count() < 2) {
            $this->command->warn('Need at least 2 schools for multi-school family scenario');
            return;
        }

        // Create teacher parent
        $teacher = $this->createPerson([
            'given_name' => 'Robert',
            'family_name' => 'Kiprotich',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(40),
            'address' => '78 Teachers Lane',
            'city' => 'Jinja',
            'district' => 'Jinja'
        ], $schools[0]);

        // Create spouse
        $spouse = $this->createPerson([
            'given_name' => 'Grace',
            'family_name' => 'Kiprotich',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(38),
            'address' => '78 Teachers Lane',
            'city' => 'Jinja',
            'district' => 'Jinja'
        ], $schools[0]);

        // Create children
        $child1 = $this->createPerson([
            'given_name' => 'Peter',
            'family_name' => 'Kiprotich',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(15),
            'address' => '78 Teachers Lane',
            'city' => 'Jinja',
            'district' => 'Jinja'
        ], $schools[0]);

        $child2 = $this->createPerson([
            'given_name' => 'Faith',
            'family_name' => 'Kiprotich',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(10),
            'address' => '78 Teachers Lane',
            'city' => 'Jinja',
            'district' => 'Jinja'
        ], $schools[1]);

        // Create family relationships
        PersonRelationship::createRelationship($teacher->id, $spouse->id, 'spouse', [
            'verification_status' => 'verified',
            'confidence_score' => 0.95
        ]);

        PersonRelationship::createRelationship($teacher->id, $child1->id, 'parent_child', [
            'verification_status' => 'verified',
            'confidence_score' => 0.98
        ]);

        PersonRelationship::createRelationship($teacher->id, $child2->id, 'parent_child', [
            'verification_status' => 'verified',
            'confidence_score' => 0.98
        ]);

        PersonRelationship::createRelationship($child1->id, $child2->id, 'sibling', [
            'verification_status' => 'verified',
            'confidence_score' => 0.95
        ]);

        // Create affiliations
        $teacherAffiliation = $this->createAffiliation($teacher, $schools[0], 'TEACHER');
        $child1Affiliation = $this->createAffiliation($child1, $schools[0], 'STUDENT');
        $child2Affiliation = $this->createAffiliation($child2, $schools[1], 'STUDENT');

        // Spouse works at SACCO
        $sacco = Organization::where('category', 'sacco')->first();
        if ($sacco) {
            $spouseSaccoAffiliation = $this->createAffiliation($spouse, $sacco, 'STAFF');
        }
    }

    /**
     * Scenario 4: SACCO member who is also parish leader
     */
    private function createSaccoParishLeaderScenario(): void
    {
        $this->command->line('Creating SACCO-parish leader scenario...');

        $sacco = Organization::where('category', 'sacco')->first();
        $parish = Organization::where('category', 'parish')->first();

        if (!$sacco || !$parish) {
            $this->command->warn('Need both SACCO and parish for this scenario');
            return;
        }

        $leader = $this->createPerson([
            'given_name' => 'Joseph',
            'family_name' => 'Mukwaya',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(45),
            'address' => '23 Community Center',
            'city' => 'Masaka',
            'district' => 'Masaka'
        ], $sacco);

        // Create affiliations
        $saccoAffiliation = $this->createAffiliation($leader, $sacco, 'BOARD_MEMBER');
        $parishAffiliation = $this->createAffiliation($leader, $parish, 'STAFF');

        // Create cross-org relationship
        CrossOrgRelationship::createCrossOrgRelationship(
            $leader->id,
            $saccoAffiliation->id,
            $parishAffiliation->id,
            [
                'relationship_strength' => 'strong',
                'impact_score' => 0.88,
                'verified' => true,
                'metadata' => ['scenario' => 'community_leader']
            ]
        );
    }

    /**
     * Scenario 5: Hospital staff family with various connections
     */
    private function createHospitalStaffFamilyScenario(): void
    {
        $this->command->line('Creating hospital staff family scenario...');

        $hospital = Organization::where('category', 'hospital')->first();
        $school = Organization::where('category', 'school')->first();
        $sacco = Organization::where('category', 'sacco')->first();

        if (!$hospital || !$school) {
            $this->command->warn('Need hospital and school for this scenario');
            return;
        }

        // Doctor parent
        $doctor = $this->createPerson([
            'given_name' => 'Dr. James',
            'family_name' => 'Ochieng',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(42),
            'address' => '12 Medical Hill',
            'city' => 'Mbale',
            'district' => 'Mbale'
        ], $hospital);

        // Nurse spouse
        $nurse = $this->createPerson([
            'given_name' => 'Agnes',
            'family_name' => 'Ochieng',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(39),
            'address' => '12 Medical Hill',
            'city' => 'Mbale',
            'district' => 'Mbale'
        ], $hospital);

        // Student child
        $student = $this->createPerson([
            'given_name' => 'Emmanuel',
            'family_name' => 'Ochieng',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(16),
            'address' => '12 Medical Hill',
            'city' => 'Mbale',
            'district' => 'Mbale'
        ], $school);

        // Create relationships
        PersonRelationship::createRelationship($doctor->id, $nurse->id, 'spouse', [
            'verification_status' => 'verified',
            'confidence_score' => 0.98
        ]);

        PersonRelationship::createRelationship($doctor->id, $student->id, 'parent_child', [
            'verification_status' => 'verified',
            'confidence_score' => 0.98
        ]);

        // Create affiliations
        $doctorAffiliation = $this->createAffiliation($doctor, $hospital, 'DOCTOR');
        $nurseAffiliation = $this->createAffiliation($nurse, $hospital, 'NURSE');
        $studentAffiliation = $this->createAffiliation($student, $school, 'STUDENT');

        if ($sacco) {
            $doctorSaccoAffiliation = $this->createAffiliation($doctor, $sacco, 'MEMBER');
        }
    }

    /**
     * Scenario 6: Complex family with business interests
     */
    private function createBusinessFamilyScenario(): void
    {
        $this->command->line('Creating business family scenario...');

        $corporate = Organization::where('category', 'corporate')->first();
        $sacco = Organization::where('category', 'sacco')->first();
        $school = Organization::where('category', 'school')->first();

        if (!$corporate || !$sacco) {
            $this->command->warn('Need corporate and SACCO organizations for this scenario');
            return;
        }

        // Business owner
        $owner = $this->createPerson([
            'given_name' => 'Patrick',
            'family_name' => 'Rwabogo',
            'gender' => 'male',
            'date_of_birth' => now()->subYears(50),
            'address' => '100 Business Park',
            'city' => 'Kampala',
            'district' => 'Kampala'
        ], $corporate);

        // Spouse who is a teacher
        $teacher = $this->createPerson([
            'given_name' => 'Susan',
            'family_name' => 'Rwabogo',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(47),
            'address' => '100 Business Park',
            'city' => 'Kampala',
            'district' => 'Kampala'
        ], $school ?? $corporate);

        // Create relationship
        PersonRelationship::createRelationship($owner->id, $teacher->id, 'spouse', [
            'verification_status' => 'verified',
            'confidence_score' => 0.98
        ]);

        // Create affiliations
        $ownerCorporateAffiliation = $this->createAffiliation($owner, $corporate, 'ADMIN');
        $ownerSaccoAffiliation = $this->createAffiliation($owner, $sacco, 'BOARD_MEMBER');

        if ($school) {
            $teacherAffiliation = $this->createAffiliation($teacher, $school, 'TEACHER');
        }

        // Create cross-org relationships
        CrossOrgRelationship::createCrossOrgRelationship(
            $owner->id,
            $ownerCorporateAffiliation->id,
            $ownerSaccoAffiliation->id,
            [
                'relationship_strength' => 'strong',
                'impact_score' => 0.92,
                'verified' => true,
                'metadata' => ['scenario' => 'business_leader_multiple_interests']
            ]
        );
    }

    /**
     * Helper method to create a person
     */
    private function createPerson(array $attributes, Organization $organization): Person
    {
        // Always create a user for the person
        $user = \App\Models\User::factory()->create();

        $data = array_merge([
            'person_id' => 'PRS-' . str_pad(Person::count() + 1, 6, '0', STR_PAD_LEFT),
            'global_identifier' => \Illuminate\Support\Str::uuid(),
            'classification' => json_encode(['diocese_scenario']),
            'country' => 'Uganda',
            'status' => 'active',
            'created_by' => '1',
            'organization_id' => $organization->id,
            'user_id' => $user->id
        ], $attributes);
        return Person::create($data);
    }

    /**
     * Helper method to create an affiliation
     */
    private function createAffiliation(Person $person, Organization $org, string $roleType): PersonAffiliation
    {
        return PersonAffiliation::create([
            'affiliation_id' => 'AFF-' . str_pad(PersonAffiliation::count() + 1, 6, '0', STR_PAD_LEFT),
            'person_id' => $person->id,
            'organization_id' => $org->id,
            'role_type' => $roleType,
            'start_date' => now()->subDays(rand(30, 730)),
            'status' => 'active',
            'created_by' => '1'
        ]);
    }
}
