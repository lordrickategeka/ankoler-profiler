<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\OrganizationSite;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Organization
        $superOrg = Organization::updateOrCreate(
            ['legal_name' => 'Ankole Profiler Super Organization'],
            [
                'display_name' => 'Ankole Diocese',
                'code' => 'SUPER001',
                'category' => 'corporate',
                'registration_number' => 'SUPER-2025-001',
                'tax_identification_number' => 'TIN-SUPER001',
                'country_of_registration' => 'UGA',
                'date_established' => '2025-01-01',
                'website_url' => 'https://ankoleprofiler.com',
                'contact_email' => 'hq@ankoleprofiler.com',
                'contact_phone' => '+256700000001',
                'description' => 'The main super Organization for Ankole Profiler.',
                'address_line_1' => 'Main Street',
                'city' => 'Mbarara',
                'district' => 'Mbarara',
                'country' => 'UGA',
                'regulatory_body' => 'Ankole Profiler Board',
                'license_number' => 'APB-SUPER-001',
                'license_issue_date' => '2025-01-01',
                'license_expiry_date' => '2030-12-31',
                'accreditation_status' => 'ACCREDITED',
                'primary_contact_name' => 'Jane Doe',
                'primary_contact_title' => 'CEO',
                'primary_contact_email' => 'jane.doe@ankoleprofiler.com',
                'primary_contact_phone' => '+256700000001',
                'bank_name' => 'Bank of Africa',
                'default_currency' => 'UGX',
                'timezone' => 'Africa/Kampala',
                'corporate_details' => [
                    'corporate_type' => 'super_Organization',
                    'ownership_type' => 'private',
                    'number_of_branches' => 0,
                ],
                'organization_type' => 'super',
                'is_super' => true,
            ]
        );
        // Hospital Organizations


        $bugema = Organization::updateOrCreate([
            'legal_name' => 'Bugema Adventist Secondary School'],
            [
                'display_name' => 'Bugema Secondary School',
                'code' => 'BASS001',
                'category' => 'school',
                'registration_number' => 'S-2023-002',
                'tax_identification_number' => 'TIN-S002',
                'country_of_registration' => 'UGA',
                'date_established' => '1948-02-01',
                'website_url' => 'https://bugema.ac.ug',
                'contact_email' => 'info@bugema.ac.ug',
                'contact_phone' => '+256455290001',
                'description' => 'Leading Seventh Day Adventist educational institution.',
                'address_line_1' => 'Bugema Road',
                'city' => 'Luwero',
                'district' => 'Luwero',
                'country' => 'UGA',
                'regulatory_body' => 'Ministry of Education and Sports',
                'license_number' => 'MOE-S-002',
                'license_issue_date' => '2023-01-01',
                'license_expiry_date' => '2025-12-31',
                'accreditation_status' => 'ACCREDITED',
                'primary_contact_name' => 'Elder Johnson Mwebembezi',
                'primary_contact_title' => 'Principal',
                'primary_contact_email' => 'principal@bugema.ac.ug',
                'primary_contact_phone' => '+256455290001',
                'bank_name' => 'Centenary Bank',
                'default_currency' => 'UGX',
                'timezone' => 'Africa/Kampala',
                'school_details' => [
                    'school_type' => 'secondary',
                    'school_level' => 'o_level_a_level',
                    'ownership' => 'faith_based',
                    'gender_composition' => 'co_educational',
                    'boarding_type' => 'boarding_school',
                    'curriculum' => 'national',
                    'student_capacity' => 2000,
                    'current_enrollment' => 1800,
                    'number_of_teachers' => 85,
                    'moe_registration_number' => 'MOE-002',
                    'uneb_center_number' => 'U1234',
                    'facilities' => [
                        'has_library' => true,
                        'has_computer_lab' => true,
                        'has_science_labs' => true,
                        'has_sports_facilities' => true,
                        'has_canteen' => true,
                        'has_medical_room' => true,
                        'has_transport' => false,
                        'has_dormitories' => true
                    ],
                    'academic_calendar' => [
                        'structure' => '3_terms',
                        'term1_start' => '2025-02-03',
                        'term1_end' => '2025-05-16',
                        'term2_start' => '2025-06-09',
                        'term2_end' => '2025-09-20',
                        'term3_start' => '2025-10-06',
                        'term3_end' => '2025-12-13'
                    ]
                ]
            ]
        );


        // Create some multi-site entries for MTN
        OrganizationSite::updateOrCreate(
            ['site_code' => 'MTN-KLA-001'],
            [
                'organization_id' => $bugema->id,
                'site_name' => 'MTN Kampala Regional Office',
                'site_type' => 'office',
                'address_line_1' => 'Commercial Plaza, Jinja Road',
                'city' => 'Kampala',
                'district' => 'Kampala',
                'country' => 'UGA',
                'site_contact_name' => 'Mr. David Obura',
                'site_contact_phone' => '+256312120100',
                'site_contact_email' => 'kampala@mtn.co.ug',
                'services_available' => ['customer_service', 'technical_support', 'sales']
            ]
        );

        OrganizationSite::updateOrCreate(
            ['site_code' => 'MTN-GUL-001'],
            [
                'organization_id' => $bugema->id,
                'site_name' => 'MTN Gulu Branch',
                'site_type' => 'branch',
                'address_line_1' => 'Main Street',
                'city' => 'Gulu',
                'district' => 'Gulu',
                'country' => 'UGA',
                'site_contact_name' => 'Ms. Grace Akello',
                'site_contact_phone' => '+256471432100',
                'site_contact_email' => 'gulu@mtn.co.ug',
                'services_available' => ['customer_service', 'sales']
            ]
        );

        echo "Sample organizations created successfully!\n";
    }
}
