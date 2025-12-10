<?php

namespace App\Exports;

use App\Models\Person;
use App\Models\Organization;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class PersonsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, WithTitle
{
    protected $organizationId;
    protected $filters;
    protected $includeFields;
    protected $organization;

    public function __construct($organizationId = null, array $filters = [], array $includeFields = [])
    {
        $this->organizationId = $organizationId;
        $this->filters = $filters;
        $this->includeFields = empty($includeFields) ? $this->getDefaultFields() : $includeFields;
        
        if ($organizationId) {
            $this->organization = Organization::find($organizationId);
        }
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $query = Person::with([
            'phones' => function($query) {
                $query->where('is_primary', true)->orWhere('status', 'active');
            },
            'emailAddresses' => function($query) {
                $query->where('is_primary', true)->orWhere('status', 'active');
            },
            'identifiers' => function($query) {
                $query->where('status', 'active');
            },
            'affiliations' => function($query) {
                if ($this->organizationId) {
                    $query->where('organization_id', $this->organizationId);
                }
                $query->where('status', 'active')->with('Organization');
            },
            'patientRecords',
            'studentRecords', 
            'saccoMemberRecords',
            'parishMemberRecords',
            'staffRecords'
        ]);

        // Filter by organization if specified
        if ($this->organizationId) {
            $query->whereHas('affiliations', function($q) {
                $q->where('organization_id', $this->organizationId)
                  ->where('status', 'active');
            });
        }

        // Apply additional filters
        if (!empty($this->filters['role_type'])) {
            $query->whereHas('affiliations', function($q) {
                $q->where('role_type', $this->filters['role_type']);
            });
        }

        if (!empty($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['age_from']) || !empty($this->filters['age_to'])) {
            $ageFrom = $this->filters['age_from'] ?? 0;
            $ageTo = $this->filters['age_to'] ?? 120;
            
            $dateFrom = now()->subYears($ageTo)->format('Y-m-d');
            $dateTo = now()->subYears($ageFrom)->format('Y-m-d');
            
            $query->whereBetween('date_of_birth', [$dateFrom, $dateTo]);
        }

        if (!empty($this->filters['city'])) {
            $query->where('city', 'like', '%' . $this->filters['city'] . '%');
        }

        if (!empty($this->filters['district'])) {
            $query->where('district', 'like', '%' . $this->filters['district'] . '%');
        }

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = [];
        
        if (in_array('basic_info', $this->includeFields)) {
            $headings = array_merge($headings, [
                'Person ID',
                'Given Name',
                'Middle Name', 
                'Family Name',
                'Full Name',
                'Date of Birth',
                'Age',
                'Gender'
            ]);
        }

        if (in_array('contact_info', $this->includeFields)) {
            $headings = array_merge($headings, [
                'Primary Phone',
                'Secondary Phone',
                'Primary Email',
                'Secondary Email'
            ]);
        }

        if (in_array('address_info', $this->includeFields)) {
            $headings = array_merge($headings, [
                'Address',
                'City',
                'District',
                'Country'
            ]);
        }

        if (in_array('identifiers', $this->includeFields)) {
            $headings = array_merge($headings, [
                'National ID',
                'Passport Number',
                'Driver\'s License',
                'Professional License'
            ]);
        }

        if (in_array('affiliation_info', $this->includeFields)) {
            $headings = array_merge($headings, [
                'Organization',
                'Role Type',
                'Role Title',
                'Site/Location',
                'Start Date',
                'Employment Status'
            ]);
        }

        if (in_array('domain_records', $this->includeFields) && $this->organization) {
            $headings = array_merge($headings, $this->getDomainSpecificHeadings());
        }

        if (in_array('metadata', $this->includeFields)) {
            $headings = array_merge($headings, [
                'Status',
                'Created Date',
                'Last Updated'
            ]);
        }

        return $headings;
    }

    /**
     * @param Person $person
     * @return array
     */
    public function map($person): array
    {
        $row = [];

        if (in_array('basic_info', $this->includeFields)) {
            $age = $person->date_of_birth ? 
                now()->diffInYears($person->date_of_birth) : null;
                
            $row = array_merge($row, [
                $person->person_id,
                $person->given_name,
                $person->middle_name,
                $person->family_name,
                $person->given_name . ' ' . ($person->middle_name ? $person->middle_name . ' ' : '') . $person->family_name,
                $person->date_of_birth ? $person->date_of_birth->format('Y-m-d') : '',
                $age,
                $person->gender ? ucfirst($person->gender) : ''
            ]);
        }

        if (in_array('contact_info', $this->includeFields)) {
            $phones = $person->phones->sortByDesc('is_primary');
            $emails = $person->emailAddresses->sortByDesc('is_primary');
            
            $row = array_merge($row, [
                $phones->first()->number ?? '',
                $phones->skip(1)->first()->number ?? '',
                $emails->first()->email ?? '',
                $emails->skip(1)->first()->email ?? ''
            ]);
        }

        if (in_array('address_info', $this->includeFields)) {
            $row = array_merge($row, [
                $person->address,
                $person->city,
                $person->district,
                $person->country
            ]);
        }

        if (in_array('identifiers', $this->includeFields)) {
            $nationalId = $person->identifiers->where('type', 'national_id')->first();
            $passport = $person->identifiers->where('type', 'passport')->first();
            $license = $person->identifiers->where('type', 'drivers_license')->first();
            $professional = $person->identifiers->where('type', 'professional_license')->first();
            
            $row = array_merge($row, [
                $nationalId->identifier ?? '',
                $passport->identifier ?? '',
                $license->identifier ?? '',
                $professional->identifier ?? ''
            ]);
        }

        if (in_array('affiliation_info', $this->includeFields)) {
            $affiliation = $this->organizationId ? 
                $person->affiliations->where('organization_id', $this->organizationId)->first() :
                $person->affiliations->first();
                
            $row = array_merge($row, [
                $affiliation->Organization->legal_name ?? '',
                $affiliation->role_type ?? '',
                $affiliation->role_title ?? '',
                $affiliation->site ?? '',
                $affiliation->start_date ? $affiliation->start_date->format('Y-m-d') : '',
                $affiliation->status ?? ''
            ]);
        }

        if (in_array('domain_records', $this->includeFields) && $this->organization) {
            $row = array_merge($row, $this->getDomainSpecificData($person));
        }

        if (in_array('metadata', $this->includeFields)) {
            $row = array_merge($row, [
                ucfirst($person->status),
                $person->created_at->format('Y-m-d H:i:s'),
                $person->updated_at->format('Y-m-d H:i:s')
            ]);
        }

        return $row;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Auto-size all columns
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'] // Blue background
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        $formats = [];
        $column = 'A';
        
        foreach ($this->headings() as $heading) {
            // Date columns
            if (str_contains(strtolower($heading), 'date') || str_contains(strtolower($heading), 'birth')) {
                $formats[$column] = NumberFormat::FORMAT_DATE_YYYYMMDD2;
            }
            // Phone columns
            elseif (str_contains(strtolower($heading), 'phone')) {
                $formats[$column] = NumberFormat::FORMAT_TEXT;
            }
            // ID columns
            elseif (str_contains(strtolower($heading), 'id') || str_contains(strtolower($heading), 'number')) {
                $formats[$column] = NumberFormat::FORMAT_TEXT;
            }
            
            $column++;
        }
        
        return $formats;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $orgName = $this->organization ? $this->organization->legal_name : 'All Organizations';
        return "Persons Export - {$orgName}";
    }

    /**
     * Get default export fields
     */
    private function getDefaultFields(): array
    {
        return ['basic_info', 'contact_info', 'address_info', 'identifiers', 'affiliation_info', 'metadata'];
    }

    /**
     * Get domain-specific headings based on organization category
     */
    private function getDomainSpecificHeadings(): array
    {
        if (!$this->organization) {
            return [];
        }

        return match($this->organization->category) {
            'hospital' => [
                'Patient Number',
                'Medical Record Number', 
                'Allergies',
                'Chronic Conditions',
                'Emergency Contact',
                'Emergency Phone',
                'Employee Number',
                'Department',
                'Specialization',
                'License Number'
            ],
            'school' => [
                'Student Number',
                'Enrollment Date',
                'Current Class',
                'Guardian Name',
                'Guardian Phone',
                'Employee Number',
                'Teaching Subjects',
                'Qualifications'
            ],
            'sacco' => [
                'Membership Number',
                'Join Date',
                'Share Capital',
                'Savings Account',
                'Next of Kin',
                'Next of Kin Phone',
                'Occupation',
                'Monthly Income'
            ],
            'parish' => [
                'Member Number',
                'Baptism Date',
                'Confirmation Date',
                'Church Group',
                'Marital Status',
                'Spouse Name',
                'Children Count',
                'Ordination Date'
            ],
            default => [
                'Employee Number',
                'Department',
                'Position',
                'Hire Date',
                'Salary',
                'Supervisor'
            ]
        };
    }

    /**
     * Get domain-specific data for a person
     */
    private function getDomainSpecificData(Person $person): array
    {
        if (!$this->organization) {
            return [];
        }

        return match($this->organization->category) {
            'hospital' => $this->getHospitalData($person),
            'school' => $this->getSchoolData($person),
            'sacco' => $this->getSaccoData($person),
            'parish' => $this->getParishData($person),
            default => $this->getCorporateData($person)
        };
    }

    private function getHospitalData(Person $person): array
    {
        $patientRecord = $person->patientRecords->first();
        $staffRecord = $person->staffRecords->first();
        
        return [
            $patientRecord?->patient_number ?? '',
            $patientRecord?->medical_record_number ?? '',
            $patientRecord?->allergies ?? '',
            $patientRecord?->chronic_conditions ?? '',
            $patientRecord?->emergency_contact_name ?? '',
            $patientRecord?->emergency_contact_phone ?? '',
            $staffRecord?->employee_number ?? '',
            $staffRecord?->department ?? '',
            $staffRecord?->specialization ?? '',
            $staffRecord?->license_number ?? ''
        ];
    }

    private function getSchoolData(Person $person): array
    {
        $studentRecord = $person->studentRecords->first();
        $staffRecord = $person->staffRecords->first();
        
        return [
            $studentRecord?->student_number ?? '',
            $studentRecord?->enrollment_date?->format('Y-m-d') ?? '',
            $studentRecord?->current_class ?? '',
            $studentRecord?->guardian_name ?? '',
            $studentRecord?->guardian_phone ?? '',
            $staffRecord?->employee_number ?? '',
            $staffRecord?->teaching_subjects ?? '',
            $staffRecord?->qualifications ?? ''
        ];
    }

    private function getSaccoData(Person $person): array
    {
        $memberRecord = $person->saccoMemberRecords->first();
        $staffRecord = $person->staffRecords->first();
        
        return [
            $memberRecord?->membership_number ?? '',
            $memberRecord?->join_date?->format('Y-m-d') ?? '',
            $memberRecord?->share_capital ?? '',
            $memberRecord?->savings_account_ref ?? '',
            $memberRecord?->next_of_kin_name ?? '',
            $memberRecord?->next_of_kin_phone ?? '',
            $memberRecord?->occupation ?? '',
            $memberRecord?->monthly_income ?? ''
        ];
    }

    private function getParishData(Person $person): array
    {
        $memberRecord = $person->parishMemberRecords->first();
        $staffRecord = $person->staffRecords->first();
        
        return [
            $memberRecord?->member_number ?? '',
            $memberRecord?->baptism_date?->format('Y-m-d') ?? '',
            $memberRecord?->confirmation_date?->format('Y-m-d') ?? '',
            $memberRecord?->church_group ?? '',
            $memberRecord?->marital_status ?? '',
            $memberRecord?->spouse_name ?? '',
            $memberRecord?->children_count ?? '',
            $staffRecord?->ordination_date?->format('Y-m-d') ?? ''
        ];
    }

    private function getCorporateData(Person $person): array
    {
        $staffRecord = $person->staffRecords->first();
        
        return [
            $staffRecord?->employee_number ?? '',
            $staffRecord?->department ?? '',
            $staffRecord?->position ?? '',
            $staffRecord?->hire_date?->format('Y-m-d') ?? '',
            $staffRecord?->salary ?? '',
            $staffRecord?->supervisor_name ?? ''
        ];
    }
}