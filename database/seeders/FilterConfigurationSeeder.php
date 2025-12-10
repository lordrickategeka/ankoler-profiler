<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FilterConfiguration;
use App\Models\Organization;

class FilterConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all organizations
        $Organizations = Organization::all();

        foreach ($Organizations as $Organization) {
            // Create some common filter configurations for each organization
            $filterConfigs = [
                [
                    'organization_id' => $Organization->id,
                    'field_name' => 'department',
                    'field_type' => 'select',
                    'field_options' => [
                        'options' => ['HR', 'Finance', 'IT', 'Operations', 'Marketing', 'Sales'],
                        'validation' => ['string', 'max:255']
                    ],
                    'is_active' => true,
                    'sort_order' => 1
                ],
                [
                    'organization_id' => $Organization->id,
                    'field_name' => 'employment_type',
                    'field_type' => 'select',
                    'field_options' => [
                        'options' => ['Full-time', 'Part-time', 'Contract', 'Intern'],
                        'validation' => ['string', 'max:255']
                    ],
                    'is_active' => true,
                    'sort_order' => 2
                ],
                [
                    'organization_id' => $Organization->id,
                    'field_name' => 'experience_years',
                    'field_type' => 'number',
                    'field_options' => [
                        'validation' => ['numeric', 'min:0', 'max:50']
                    ],
                    'is_active' => true,
                    'sort_order' => 3
                ],
                [
                    'organization_id' => $Organization->id,
                    'field_name' => 'location',
                    'field_type' => 'text',
                    'field_options' => [
                        'validation' => ['string', 'max:255']
                    ],
                    'is_active' => true,
                    'sort_order' => 4
                ]
            ];

            // Create additional organization-specific filters based on category
            if ($Organization->category === 'HEALTH') {
                $filterConfigs[] = [
                    'organization_id' => $Organization->id,
                    'field_name' => 'medical_specialization',
                    'field_type' => 'select',
                    'field_options' => [
                        'options' => ['General Medicine', 'Pediatrics', 'Surgery', 'Cardiology', 'Neurology', 'Orthopedics'],
                        'validation' => ['string', 'max:255']
                    ],
                    'is_active' => true,
                    'sort_order' => 5
                ];
            }

            if ($Organization->category === 'EDUCATION') {
                $filterConfigs[] = [
                    'organization_id' => $Organization->id,
                    'field_name' => 'subject_area',
                    'field_type' => 'select',
                    'field_options' => [
                        'options' => ['Mathematics', 'Science', 'English', 'History', 'Arts', 'Physical Education'],
                        'validation' => ['string', 'max:255']
                    ],
                    'is_active' => true,
                    'sort_order' => 5
                ];
            }

            if ($Organization->category === 'FINANCE') {
                $filterConfigs[] = [
                    'organization_id' => $Organization->id,
                    'field_name' => 'account_type',
                    'field_type' => 'select',
                    'field_options' => [
                        'options' => ['Savings', 'Current', 'Fixed Deposit', 'Loan'],
                        'validation' => ['string', 'max:255']
                    ],
                    'is_active' => true,
                    'sort_order' => 5
                ];
            }

            // Insert all filter configurations
            foreach ($filterConfigs as $config) {
                FilterConfiguration::updateOrCreate(
                    [
                        'organization_id' => $config['organization_id'],
                        'field_name' => $config['field_name']
                    ],
                    $config
                );
            }
        }
    }
}
