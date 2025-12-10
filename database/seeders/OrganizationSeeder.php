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
                'display_name' => 'Ankole Profiler HQ',
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
        $bugema = Organization::updateOrCreate(
            ['legal_name' => 'Bugema Adventist Secondary School'],
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
                    'boarding_type' => 'boarding',
                    'curriculum' => 'national',
                    'student_capacity' => 2000,
                    'current_enrollment' => 1800,
                    'number_of_teachers' => 80,
                    'moe_registration_number' => 'MOE-002',
                    'facilities' => [
                        'has_library' => true,
                        'has_computer_lab' => true,
                        'has_science_labs' => true,
                        'has_sports_facilities' => true,
                        'has_canteen' => true,
                        'has_medical_room' => true,
                        'has_transport' => true,
                        'has_dormitories' => true
                    ]
                ]
            ]
        );

        $nsambya = Organization::updateOrCreate([
            'legal_name' => 'St. Francis Hospital Nsambya'],
            [
                'display_name' => 'Nsambya Hospital',
                'code' => 'SFHN001',
                'category' => 'hospital',
                'registration_number' => 'H-2023-002',
                'tax_identification_number' => 'TIN-H002',
                'country_of_registration' => 'UGA',
                'date_established' => '1903-03-19',
                'website_url' => 'https://nsambyahospital.org',
                'contact_email' => 'info@nsambyahospital.org',
                'contact_phone' => '+256414567890',
                'description' => 'Leading private faith-based hospital providing quality healthcare services.',
                'address_line_1' => 'Nsambya Hill',
                'city' => 'Kampala',
                'district' => 'Kampala',
                'country' => 'UGA',
                'regulatory_body' => 'Ministry of Health',
                'license_number' => 'MOH-H-002',
                'license_issue_date' => '2023-01-01',
                'license_expiry_date' => '2025-12-31',
                'accreditation_status' => 'ACCREDITED',
                'primary_contact_name' => 'Dr. Martin Sekimpi',
                'primary_contact_title' => 'Medical Director',
                'primary_contact_email' => 'md@nsambyahospital.org',
                'primary_contact_phone' => '+256414567890',
                'bank_name' => 'Stanbic Bank',
                'default_currency' => 'UGX',
                'timezone' => 'Africa/Kampala',
                'hospital_details' => [
                    'hospital_type' => 'general_hospital',
                    'ownership_type' => 'faith_based',
                    'bed_capacity' => 300,
                    'operating_theaters' => 6,
                    'has_emergency_department' => true,
                    'has_icu' => true,
                    'has_laboratory' => true,
                    'has_pharmacy' => true,
                    'has_radiology' => true,
                    'has_ambulance' => true,
                    'medical_license_number' => 'ML-2023-002',
                    'moh_registration_number' => 'MOH-002',
                    'nhis_accreditation' => true,
                    'insurance_providers' => ['NHIS', 'AAR', 'APA', 'UAP'],
                    'specializations' => ['surgery', 'internal_medicine', 'pediatrics', 'obstetrics_gynecology'],
                    'accreditations' => ['JCI', 'ISO_9001']
                ]
            ]
        );

        // School Organizations
        $makerere = Organization::updateOrCreate([
            'legal_name' => 'Makerere University'],
            [
                'display_name' => 'Makerere University',
                'code' => 'MAK001',
                'category' => 'school',
                'registration_number' => 'S-2023-001',
                'tax_identification_number' => 'TIN-S001',
                'country_of_registration' => 'UGA',
                'date_established' => '1922-01-01',
                'website_url' => 'https://www.mak.ac.ug',
                'contact_email' => 'info@mak.ac.ug',
                'contact_phone' => '+256414532631',
                'description' => 'Uganda\'s oldest and most prestigious university.',
                'address_line_1' => 'University Road',
                'city' => 'Kampala',
                'district' => 'Kampala',
                'country' => 'UGA',
                'regulatory_body' => 'Ministry of Education and Sports',
                'license_number' => 'MOE-U-001',
                'license_issue_date' => '2023-01-01',
                'license_expiry_date' => '2026-12-31',
                'accreditation_status' => 'ACCREDITED',
                'primary_contact_name' => 'Prof. Barnabas Nawangwe',
                'primary_contact_title' => 'Vice Chancellor',
                'primary_contact_email' => 'vc@mak.ac.ug',
                'primary_contact_phone' => '+256414532631',
                'bank_name' => 'Bank of Uganda',
                'default_currency' => 'UGX',
                'timezone' => 'Africa/Kampala',
                'school_details' => [
                    'school_type' => 'university',
                    'school_level' => 'university',
                    'ownership' => 'government',
                    'gender_composition' => 'co_educational',
                    'boarding_type' => 'mixed',
                    'curriculum' => 'national',
                    'student_capacity' => 40000,
                    'current_enrollment' => 35000,
                    'number_of_teachers' => 1200,
                    'moe_registration_number' => 'MOE-001',
                    'facilities' => [
                        'has_library' => true,
                        'has_computer_lab' => true,
                        'has_science_labs' => true,
                        'has_sports_facilities' => true,
                        'has_canteen' => true,
                        'has_medical_room' => true,
                        'has_transport' => true,
                        'has_dormitories' => true
                    ]
                ]
            ]
        );

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

        // SACCO Organizations
        $wazalendo = Organization::updateOrCreate(
            ['legal_name' => 'Wazalendo Teachers Savings and Credit Cooperative Society'],
            [
            'legal_name' => 'Wazalendo Teachers Savings and Credit Cooperative Society',
            'display_name' => 'Wazalendo Teachers SACCO',
            'code' => 'WTSACCO001',
            'category' => 'sacco',
            'registration_number' => 'SC-2023-001',
            'tax_identification_number' => 'TIN-SC001',
            'country_of_registration' => 'UGA',
            'date_established' => '2010-03-15',
            'website_url' => 'https://wazalendosacco.ug',
            'contact_email' => 'info@wazalendosacco.ug',
            'contact_phone' => '+256414258369',
            'description' => 'Dedicated SACCO serving teachers across Uganda.',
            'address_line_1' => 'Teachers House, Kira Road',
            'city' => 'Kampala',
            'district' => 'Kampala',
            'country' => 'UGA',
            'regulatory_body' => 'Ministry of Trade, Industry and Cooperatives',
            'license_number' => 'MTIC-SC-001',
            'license_issue_date' => '2023-01-01',
            'license_expiry_date' => '2025-12-31',
            'accreditation_status' => 'ACCREDITED',
            'primary_contact_name' => 'Mr. Patrick Mugenyi',
            'primary_contact_title' => 'General Manager',
            'primary_contact_email' => 'gm@wazalendosacco.ug',
            'primary_contact_phone' => '+256414258369',
            'bank_name' => 'DFCU Bank',
            'default_currency' => 'UGX',
            'timezone' => 'Africa/Kampala',
            'sacco_details' => [
                'sacco_type' => 'teachers',
                'membership_type' => 'closed',
                'bond_of_association' => 'teachers',
                'registration_authority' => 'ministry_of_trade',
                'tier_level' => 'tier_2',
                'date_of_first_registration' => '2010-03-15',
                'certificate_of_incorporation' => 'CI-2010-001',
                'minimum_share_capital' => 50000,
                'share_value' => 5000,
                'minimum_shares' => 10,
                'maximum_shares' => 2000,
                'entrance_fee' => 10000,
                'registration_fee' => 5000,
                'current_total_members' => 15000,
                'active_members' => 14200,
                'total_share_capital' => 750000000,
                'total_savings' => 12000000000,
                'total_loans_outstanding' => 8000000000,
                'savings_products' => ['ordinary', 'fixed_deposit', 'retirement'],
                'loan_products' => ['development', 'emergency', 'school_fees', 'salary'],
                'interest_rates' => [
                    'savings_rate' => 8.0,
                    'loan_rate' => 15.0,
                    'penalty_rate' => 3.0,
                    'processing_fee' => 1.5
                ],
                'loan_terms' => [
                    'minimum_amount' => 100000,
                    'maximum_amount' => 10000000,
                    'minimum_period_months' => 6,
                    'maximum_period_months' => 48,
                    'loan_to_share_ratio' => '4:1'
                ],
                'services' => [
                    'number_of_branches' => 8,
                    'mobile_money_integration' => true,
                    'mobile_money_providers' => ['MTN', 'Airtel'],
                    'agency_banking' => true,
                    'atm_services' => false,
                    'online_banking' => true,
                    'core_banking_system' => 'Craft Silicon'
                ],
                'governance' => [
                    'board_size' => 9,
                    'election_frequency_years' => 3,
                    'agm_month' => 'March',
                    'audit_firm' => 'Ernst & Young',
                    'last_audit_date' => '2024-06-30'
                ]
            ]
        ]);

        // Parish Organizations
        $rubaga = Organization::updateOrCreate(
            ['legal_name' => 'Rubaga Cathedral Parish'],
            [
            'legal_name' => 'Rubaga Cathedral Parish',
            'display_name' => 'Rubaga Cathedral',
            'code' => 'RCP001',
            'category' => 'parish',
            'registration_number' => 'P-2023-001',
            'tax_identification_number' => 'TIN-P001',
            'country_of_registration' => 'UGA',
            'date_established' => '1914-12-31',
            'contact_email' => 'info@rubagacathedral.org',
            'contact_phone' => '+256414270326',
            'description' => 'The seat of the Catholic Archdiocese of Kampala.',
            'address_line_1' => 'Rubaga Hill',
            'city' => 'Kampala',
            'district' => 'Kampala',
            'country' => 'UGA',
            'primary_contact_name' => 'Rev. Fr. James Kiwanuka',
            'primary_contact_title' => 'Parish Priest',
            'primary_contact_email' => 'priest@rubagacathedral.org',
            'primary_contact_phone' => '+256414270326',
            'bank_name' => 'Centenary Bank',
            'default_currency' => 'UGX',
            'timezone' => 'Africa/Kampala',
            'parish_details' => [
                'denomination' => 'catholic',
                'church_type' => 'cathedral',
                'archdiocese_diocese' => 'Kampala Archdiocese',
                'patron_saint' => 'St. Mary',
                'registered_members' => 8000,
                'active_members' => 6500,
                'sub_parishes' => 12,
                'outstations' => 25
            ]
        ]);

        // Corporate Organizations
        $mtn = Organization::updateOrCreate(
            ['legal_name' => 'MTN Uganda Limited'],
            [
            'legal_name' => 'MTN Uganda Limited',
            'display_name' => 'MTN Uganda',
            'code' => 'MTNUG001',
            'category' => 'corporate',
            'registration_number' => 'C-2023-001',
            'tax_identification_number' => 'TIN-C001',
            'country_of_registration' => 'UGA',
            'date_established' => '1998-10-01',
            'website_url' => 'https://www.mtn.co.ug',
            'contact_email' => 'info@mtn.co.ug',
            'contact_phone' => '+256312120000',
            'description' => 'Leading telecommunications company in Uganda.',
            'address_line_1' => 'MTN Towers, Hannington Road',
            'city' => 'Kampala',
            'district' => 'Kampala',
            'country' => 'UGA',
            'regulatory_body' => 'Uganda Communications Commission',
            'license_number' => 'UCC-TEL-001',
            'license_issue_date' => '2023-01-01',
            'license_expiry_date' => '2028-12-31',
            'accreditation_status' => 'ACCREDITED',
            'primary_contact_name' => 'Ms. Sylvia Mulinge',
            'primary_contact_title' => 'Chief Executive Officer',
            'primary_contact_email' => 'ceo@mtn.co.ug',
            'primary_contact_phone' => '+256312120000',
            'bank_name' => 'Standard Chartered Bank',
            'default_currency' => 'UGX',
            'timezone' => 'Africa/Kampala',
            'is_multi_site' => true,
            'corporate_details' => [
                'company_type' => 'limited',
                'industry_sector' => 'telecommunications',
                'number_of_employees' => 2500,
                'annual_turnover' => 1200000000000,
                'business_activities' => ['mobile_telephony', 'data_services', 'mobile_money', 'enterprise_solutions'],
                'certifications' => ['ISO_9001', 'ISO_27001']
            ]
        ]);

        // Create some multi-site entries for MTN
        OrganizationSite::updateOrCreate(
            ['site_code' => 'MTN-KLA-001'],
            [
                'organization_id' => $mtn->id,
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
                'organization_id' => $mtn->id,
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
