<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Organizations\Create;

class OrganizationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_creation_form_renders()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/organizations/create')
            ->assertStatus(200)
            ->assertSeeLivewire('organizations.create');
    }

    public function test_can_create_hospital_organization()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('category', 'hospital')
            ->set('legal_name', 'Mulago National Referral Hospital')
            ->set('display_name', 'Mulago Hospital')
            ->set('code', 'MNH001')
            ->set('registration_number', 'HOSP/2023/001')
            ->set('date_established', '1962-01-15')
            ->set('contact_email', 'info@mulagohospital.go.ug')
            ->set('contact_phone', '+256414530692')
            ->set('address_line_1', 'Mulago Hill')
            ->set('city', 'Kampala')
            ->set('country', 'UGA')
            ->set('primary_contact_name', 'Dr. Byarugaba Baterana')
            ->set('primary_contact_email', 'director@mulagohospital.go.ug')
            ->set('primary_contact_phone', '+256782123456')
            ->set('categoryDetails.hospital_type', 'REFERRAL')
            ->set('categoryDetails.bed_capacity', 1500)
            ->set('categoryDetails.level_of_care', 'TERTIARY')
            ->set('categoryDetails.emergency_services', 1)
            ->set('categoryDetails.medical_director', 'Dr. Byarugaba Baterana')
            ->call('submit')
            ->assertRedirect('/organizations');

        $this->assertDatabaseHas('Organizations', [
            'legal_name' => 'Mulago National Referral Hospital',
            'category' => 'hospital',
            'code' => 'MNH001'
        ]);
    }

    public function test_can_create_school_organization()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('category', 'school')
            ->set('legal_name', 'Makerere University')
            ->set('display_name', 'Makerere University')
            ->set('code', 'MAK001')
            ->set('registration_number', 'UNIV/1922/001')
            ->set('date_established', '1922-06-01')
            ->set('contact_email', 'info@mak.ac.ug')
            ->set('contact_phone', '+256414532694')
            ->set('address_line_1', 'Makerere Hill')
            ->set('city', 'Kampala')
            ->set('country', 'UGA')
            ->set('primary_contact_name', 'Prof. Barnabas Nawangwe')
            ->set('primary_contact_email', 'vc@mak.ac.ug')
            ->set('primary_contact_phone', '+256782654321')
            ->set('categoryDetails.school_type', 'UNIVERSITY')
            ->set('categoryDetails.education_level', 'TERTIARY')
            ->set('categoryDetails.student_capacity', 50000)
            ->set('categoryDetails.current_enrollment', 40000)
            ->set('categoryDetails.number_of_teachers', 1500)
            ->set('categoryDetails.principal_name', 'Prof. Barnabas Nawangwe')
            ->call('submit')
            ->assertRedirect('/organizations');

        $this->assertDatabaseHas('Organizations', [
            'legal_name' => 'Makerere University',
            'category' => 'school',
            'code' => 'MAK001'
        ]);
    }

    public function test_validation_rules_work()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('category', 'hospital')
            ->set('legal_name', '') // Required field
            ->set('contact_email', 'invalid-email') // Invalid email
            ->call('submit')
            ->assertHasErrors(['legal_name', 'contact_email']);
    }

    public function test_multi_step_navigation_works()
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(Create::class);

        // Start at step 1
        $component->assertSet('currentStep', 1);

        // Can't proceed without selecting category
        $component->call('nextStep')
            ->assertHasErrors(['category']);

        // Select category and proceed
        $component->set('category', 'hospital')
            ->call('nextStep')
            ->assertSet('currentStep', 2);

        // Can go back to previous step
        $component->call('previousStep')
            ->assertSet('currentStep', 1);
    }

    public function test_category_specific_fields_are_validated()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('category', 'hospital')
            ->set('currentStep', 5) // Go to category-specific step
            ->call('validateCurrentStep')
            ->assertHasErrors(['categoryDetails.hospital_type', 'categoryDetails.bed_capacity']);
    }
}
