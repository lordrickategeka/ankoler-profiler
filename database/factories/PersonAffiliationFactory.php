<?php

namespace Database\Factories;

use App\Models\PersonAffiliation;
use App\Models\Person;
use App\Models\Organisation;
use App\Models\OrganisationSite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonAffiliation>
 */
class PersonAffiliationFactory extends Factory
{
    protected $model = PersonAffiliation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'organisation_id' => 1, // Will be overridden in the seeder
            'site' => null, // Will be set based on organization
            'role_type' => 'STAFF',
            'role_title' => $this->faker->jobTitle(),
            'start_date' => $this->faker->dateTimeBetween('-5 years', '-1 month')->format('Y-m-d'),
            'end_date' => null,
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    /**
     * Configure for hospital organization
     */
    public function forHospital(Organisation $hospital): static
    {
        return $this->state(function (array $attributes) use ($hospital) {
            $roles = ['PATIENT', 'DOCTOR', 'NURSE', 'STAFF', 'ADMIN'];
            $roleType = $this->faker->randomElement($roles);
            
            $roleTitles = [
                'PATIENT' => ['Patient', 'Outpatient', 'Inpatient'],
                'DOCTOR' => ['Medical Officer', 'Specialist', 'Consultant', 'Resident Doctor', 'Senior Consultant'],
                'NURSE' => ['Registered Nurse', 'Senior Nurse', 'Nursing Officer', 'Midwife', 'Critical Care Nurse'],
                'STAFF' => ['Laboratory Technician', 'Pharmacist', 'Physiotherapist', 'Radiographer', 'Administrative Officer'],
                'ADMIN' => ['Hospital Administrator', 'Medical Superintendent', 'Finance Officer', 'HR Officer'],
            ];

            return [
                'organisation_id' => $hospital->id,
                'role_type' => $roleType,
                'role_title' => $this->faker->randomElement($roleTitles[$roleType]),
            ];
        });
    }

    /**
     * Configure for school organization
     */
    public function forSchool(Organisation $school): static
    {
        return $this->state(function (array $attributes) use ($school) {
            $roles = ['STUDENT', 'TEACHER', 'STAFF', 'ADMIN'];
            $roleType = $this->faker->randomElement($roles);
            
            $roleTitles = [
                'STUDENT' => ['Primary Student', 'Secondary Student', 'University Student', 'Graduate Student'],
                'TEACHER' => ['Primary Teacher', 'Secondary Teacher', 'Senior Teacher', 'Head of Department', 'Lecturer'],
                'STAFF' => ['Laboratory Assistant', 'Librarian', 'Sports Coach', 'Counselor', 'Security Guard'],
                'ADMIN' => ['Headteacher', 'Deputy Headteacher', 'Academic Registrar', 'Bursar', 'Dean'],
            ];

            return [
                'organisation_id' => $school->id,
                'role_type' => $roleType,
                'role_title' => $this->faker->randomElement($roleTitles[$roleType]),
            ];
        });
    }

    /**
     * Configure for SACCO organization
     */
    public function forSacco(Organisation $sacco): static
    {
        return $this->state(function (array $attributes) use ($sacco) {
            $roles = ['MEMBER', 'STAFF', 'ADMIN', 'BOARD_MEMBER'];
            $roleType = $this->faker->randomElement($roles);
            
            $roleTitles = [
                'MEMBER' => ['SACCO Member', 'Savings Member', 'Credit Member', 'Active Member'],
                'STAFF' => ['Credit Officer', 'Accounts Officer', 'Field Officer', 'Customer Service Officer'],
                'ADMIN' => ['Manager', 'Assistant Manager', 'Finance Manager', 'Operations Manager'],
                'BOARD_MEMBER' => ['Chairperson', 'Vice Chairperson', 'Secretary', 'Treasurer', 'Board Member'],
            ];

            return [
                'organisation_id' => $sacco->id,
                'role_type' => $roleType,
                'role_title' => $this->faker->randomElement($roleTitles[$roleType]),
            ];
        });
    }

    /**
     * Configure for parish organization
     */
    public function forParish(Organisation $parish): static
    {
        return $this->state(function (array $attributes) use ($parish) {
            $roles = ['MEMBER', 'CLERGY', 'STAFF', 'ADMIN'];
            $roleType = $this->faker->randomElement($roles);
            
            $roleTitles = [
                'MEMBER' => ['Parish Member', 'Youth Member', 'Women Group Member', 'Men Group Member'],
                'CLERGY' => ['Parish Priest', 'Assistant Priest', 'Deacon', 'Catechist'],
                'STAFF' => ['Secretary', 'Accountant', 'Organist', 'Choir Director'],
                'ADMIN' => ['Parish Council Chairperson', 'Vice Chairperson', 'Secretary', 'Treasurer'],
            ];

            return [
                'organisation_id' => $parish->id,
                'role_type' => $roleType,
                'role_title' => $this->faker->randomElement($roleTitles[$roleType]),
            ];
        });
    }

    /**
     * Configure for corporate organization
     */
    public function forCorporate(Organisation $corporate): static
    {
        return $this->state(function (array $attributes) use ($corporate) {
            $roles = ['EMPLOYEE', 'MANAGER', 'ADMIN'];
            $roleType = $this->faker->randomElement($roles);
            
            $roleTitles = [
                'EMPLOYEE' => ['Software Developer', 'Accountant', 'Sales Representative', 'Customer Service Officer', 'Marketing Officer'],
                'MANAGER' => ['Department Manager', 'Team Lead', 'Project Manager', 'Regional Manager', 'Operations Manager'],
                'ADMIN' => ['CEO', 'Managing Director', 'General Manager', 'Finance Director', 'HR Director'],
            ];

            return [
                'organisation_id' => $corporate->id,
                'role_type' => $roleType,
                'role_title' => $this->faker->randomElement($roleTitles[$roleType]),
            ];
        });
    }
}