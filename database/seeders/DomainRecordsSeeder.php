<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DomainRecordsSeeder extends Seeder
{
    public function run()
    {
        // First create some person affiliations that the domain records can reference
        $affiliationIds = [];

        // Create basic person affiliations (assuming some persons and Organizations exist)
        for ($i = 1; $i <= 5; $i++) {
            $affiliationId = DB::table('person_affiliations')->insertGetId([
                'affiliation_id' => 'AFF-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'person_id' => 1, // Assuming person with ID 1 exists
                'organization_id' => 1, // Assuming Organization with ID 1 exists
                'role_type' => ['STAFF', 'STUDENT', 'PATIENT', 'MEMBER', 'MEMBER'][$i-1],
                'start_date' => now()->subDays(30),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $affiliationIds[] = $affiliationId;
        }

        // Seed staff_records
        DB::table('staff_records')->insert([
            [
                'id' => Str::uuid(),
                'affiliation_id' => $affiliationIds[0],
                'staff_number' => 'STF-0001',
                'payroll_id' => 'PR-001',
                'employment_type' => 'FULL_TIME',
                'grade' => 'A',
                'contract_start' => '2025-01-01',
                'contract_end' => '2026-01-01',
                'supervisor_id' => null,
                'work_schedule' => json_encode(['Mon-Fri' => '8am-5pm']),
                'hr_notes' => json_encode(['note' => 'First staff record']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed student_records
        DB::table('student_records')->insert([
            [
                'id' => Str::uuid(),
                'affiliation_id' => $affiliationIds[1],
                'student_number' => 'STD-0001',
                'enrollment_date' => '2024-09-01',
                'graduation_date' => null,
                'current_class' => 'Computer Science Year 3',
                'guardian_contact' => json_encode(['name' => 'John Doe Parent', 'phone' => '+256700123456']),
                'academic_notes' => json_encode(['note' => 'First student record']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed patient_records
        DB::table('patient_records')->insert([
            [
                'id' => Str::uuid(),
                'affiliation_id' => $affiliationIds[2],
                'patient_number' => 'PAT-0001',
                'medical_record_number' => 'MRN-001',
                'primary_physician_id' => null,
                'primary_care_unit_id' => null,
                'allergies' => 'None',
                'chronic_conditions' => 'None',
                'last_visit' => now(),
                'clinical_notes' => json_encode(['note' => 'First patient record']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed sacco_member_records
        DB::table('sacco_member_records')->insert([
            [
                'id' => Str::uuid(),
                'affiliation_id' => $affiliationIds[3],
                'membership_number' => 'SACCO-0001',
                'join_date' => '2023-05-01',
                'share_capital' => 100000.00,
                'savings_account_ref' => 'SAV-001',
                'sacco_notes' => json_encode(['note' => 'First sacco member record']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed parish_member_records
        DB::table('parish_member_records')->insert([
            [
                'id' => Str::uuid(),
                'affiliation_id' => $affiliationIds[4],
                'member_number' => 'PARISH-0001',
                'communion_status' => 'active',
                'baptism_date' => '2022-01-01',
                'parish_notes' => json_encode(['note' => 'First parish member record']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
