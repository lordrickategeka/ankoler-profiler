<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Person;
use App\Exports\PersonsExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class PersonExportService
{
    /**
     * Export persons to Excel format
     */
    public function exportToExcel($organizationId = null, array $filters = [], array $includeFields = []): string
    {
        $export = new PersonsExport($organizationId, $filters, $includeFields);
        
        $filename = $this->generateFilename($organizationId, 'xlsx');
        $path = "exports/persons/{$filename}";
        
        // Store in storage
        Excel::store($export, $path, 'local');
        
        return storage_path("app/{$path}");
    }

    /**
     * Export persons to CSV format
     */
    public function exportToCsv($organizationId = null, array $filters = [], array $includeFields = []): string
    {
        $export = new PersonsExport($organizationId, $filters, $includeFields);
        
        $filename = $this->generateFilename($organizationId, 'csv');
        $path = "exports/persons/{$filename}";
        
        // Store in storage
        Excel::store($export, $path, 'local', \Maatwebsite\Excel\Excel::CSV);
        
        return storage_path("app/{$path}");
    }

    /**
     * Get export statistics
     */
    public function getExportStats($organizationId = null, array $filters = []): array
    {
        $query = Person::query();

        // Filter by organization if specified
        if ($organizationId) {
            $query->whereHas('affiliations', function($q) use ($organizationId) {
                $q->where('organisation_id', $organizationId)
                  ->where('status', 'active');
            });
        }

        // Apply filters
        $this->applyFilters($query, $filters);

        $totalPersons = $query->count();
        
        // Get role distribution
        $roleDistribution = [];
        if ($organizationId) {
            $roleDistribution = $query->join('person_affiliations', 'persons.id', '=', 'person_affiliations.person_id')
                ->where('person_affiliations.organisation_id', $organizationId)
                ->where('person_affiliations.status', 'active')
                ->selectRaw('role_type, COUNT(*) as count')
                ->groupBy('role_type')
                ->pluck('count', 'role_type')
                ->toArray();
        }

        // Get gender distribution
        $genderDistribution = $query->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        // Get age distribution
        $ageRanges = [
            '0-18' => [$this->getDateFromAge(18), $this->getDateFromAge(0)],
            '19-30' => [$this->getDateFromAge(30), $this->getDateFromAge(19)],
            '31-50' => [$this->getDateFromAge(50), $this->getDateFromAge(31)],
            '51-65' => [$this->getDateFromAge(65), $this->getDateFromAge(51)],
            '65+' => ['1900-01-01', $this->getDateFromAge(65)]
        ];

        $ageDistribution = [];
        foreach ($ageRanges as $range => $dates) {
            $count = (clone $query)->whereBetween('date_of_birth', $dates)->count();
            if ($count > 0) {
                $ageDistribution[$range] = $count;
            }
        }

        return [
            'total_persons' => $totalPersons,
            'role_distribution' => $roleDistribution,
            'gender_distribution' => $genderDistribution,
            'age_distribution' => $ageDistribution,
            'organization' => $organizationId ? Organisation::find($organizationId) : null
        ];
    }

    /**
     * Get available export fields based on organization category
     */
    public function getAvailableFields($organizationId = null): array
    {
        $baseFields = [
            'basic_info' => [
                'label' => 'Basic Information',
                'description' => 'Name, date of birth, gender, age',
                'default' => true
            ],
            'contact_info' => [
                'label' => 'Contact Information',
                'description' => 'Phone numbers and email addresses',
                'default' => true
            ],
            'address_info' => [
                'label' => 'Address Information',
                'description' => 'Physical address, city, district, country',
                'default' => true
            ],
            'identifiers' => [
                'label' => 'Identity Documents',
                'description' => 'National ID, passport, licenses',
                'default' => true
            ],
            'affiliation_info' => [
                'label' => 'Organization Affiliation',
                'description' => 'Role, title, start date, status',
                'default' => true
            ],
            'metadata' => [
                'label' => 'System Information',
                'description' => 'Status, created date, last updated',
                'default' => false
            ]
        ];

        if ($organizationId) {
            $organization = Organisation::find($organizationId);
            if ($organization) {
                $baseFields['domain_records'] = [
                    'label' => ucfirst($organization->category) . '-Specific Data',
                    'description' => $this->getDomainDescription($organization->category),
                    'default' => true
                ];
            }
        }

        return $baseFields;
    }

    /**
     * Get available filters based on organization
     */
    public function getAvailableFilters($organizationId = null): array
    {
        $filters = [
            'role_type' => [
                'label' => 'Role Type',
                'type' => 'select',
                'options' => $this->getRoleOptions($organizationId)
            ],
            'gender' => [
                'label' => 'Gender',
                'type' => 'select',
                'options' => [
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                    'prefer_not_to_say' => 'Prefer not to say'
                ]
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'suspended' => 'Suspended'
                ]
            ],
            'age_from' => [
                'label' => 'Age From',
                'type' => 'number',
                'min' => 0,
                'max' => 120
            ],
            'age_to' => [
                'label' => 'Age To',
                'type' => 'number',
                'min' => 0,
                'max' => 120
            ],
            'city' => [
                'label' => 'City',
                'type' => 'text',
                'placeholder' => 'Enter city name'
            ],
            'district' => [
                'label' => 'District',
                'type' => 'text',
                'placeholder' => 'Enter district name'
            ]
        ];

        return $filters;
    }

    /**
     * Generate filename for export
     */
    private function generateFilename($organizationId = null, string $extension = 'xlsx'): string
    {
        $orgCode = 'ALL';
        if ($organizationId) {
            $organization = Organisation::find($organizationId);
            $orgCode = $organization ? Str::slug($organization->code ?? $organization->legal_name) : 'ORG';
        }
        
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "persons_export_{$orgCode}_{$timestamp}.{$extension}";
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['role_type'])) {
            $query->whereHas('affiliations', function($q) use ($filters) {
                $q->where('role_type', $filters['role_type']);
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['age_from']) || !empty($filters['age_to'])) {
            $ageFrom = $filters['age_from'] ?? 0;
            $ageTo = $filters['age_to'] ?? 120;
            
            $dateFrom = $this->getDateFromAge($ageTo);
            $dateTo = $this->getDateFromAge($ageFrom);
            
            $query->whereBetween('date_of_birth', [$dateFrom, $dateTo]);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['district'])) {
            $query->where('district', 'like', '%' . $filters['district'] . '%');
        }
    }

    /**
     * Get date from age
     */
    private function getDateFromAge(int $age): string
    {
        return now()->subYears($age)->format('Y-m-d');
    }

    /**
     * Get role options based on organization
     */
    private function getRoleOptions($organizationId = null): array
    {
        if (!$organizationId) {
            return [
                'STAFF' => 'Staff',
                'ADMIN' => 'Administrator',
                'MEMBER' => 'Member'
            ];
        }

        $organization = Organisation::find($organizationId);
        if (!$organization) {
            return [];
        }

        return match($organization->category) {
            'hospital' => [
                'PATIENT' => 'Patient',
                'DOCTOR' => 'Doctor',
                'NURSE' => 'Nurse',
                'STAFF' => 'Staff',
                'ADMIN' => 'Administrator'
            ],
            'school' => [
                'STUDENT' => 'Student',
                'TEACHER' => 'Teacher',
                'STAFF' => 'Staff',
                'ADMIN' => 'Administrator'
            ],
            'sacco' => [
                'MEMBER' => 'Member',
                'STAFF' => 'Staff',
                'ADMIN' => 'Administrator',
                'BOARD_MEMBER' => 'Board Member'
            ],
            'parish' => [
                'MEMBER' => 'Member',
                'CLERGY' => 'Clergy',
                'STAFF' => 'Staff',
                'ADMIN' => 'Administrator'
            ],
            default => [
                'EMPLOYEE' => 'Employee',
                'MANAGER' => 'Manager',
                'ADMIN' => 'Administrator'
            ]
        };
    }

    /**
     * Get domain description for organization category
     */
    private function getDomainDescription(string $category): string
    {
        return match($category) {
            'hospital' => 'Patient records, medical information, staff details',
            'school' => 'Student records, enrollment data, teaching information',
            'sacco' => 'Membership details, financial information, savings data',
            'parish' => 'Member records, religious information, church activities',
            default => 'Employee records, department information, work details'
        };
    }

    /**
     * Clean up old export files
     */
    public function cleanupOldExports(int $daysOld = 7): int
    {
        $exportPath = storage_path('app/exports/persons');
        
        if (!is_dir($exportPath)) {
            return 0;
        }

        $files = glob($exportPath . '/*');
        $deletedCount = 0;
        $cutoffTime = now()->subDays($daysOld)->timestamp;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}