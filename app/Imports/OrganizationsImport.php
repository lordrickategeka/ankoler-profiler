<?php

namespace App\Imports;

use App\Models\Organization;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class OrganizationsImport implements ToCollection, WithHeadingRow, WithValidation, WithStartRow, SkipsEmptyRows
{
    use Importable;

    public array $results = [
        'summary' => [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
        ],
        'details' => []
    ];

    private $fieldMapping = [
        'Category' => 'category',
        'Legal name' => 'legal_name',
        'Organization type' => 'organization_type',
        'Registration number' => 'registration_number',
        'Contact email' => 'contact_email',
        'Contact phone' => 'contact_phone',
        'Date established' => 'date_established',
        'Country' => 'country',
        'City' => 'city',
        'Address' => 'address',
        'Primary Contact name' => 'primary_contact_name',
        'Primary contact email' => 'primary_contact_email',
        'Primary contact phone' => 'primary_contact_phone',
    ];

    public function collection(Collection $collection)
    {
        $this->results['summary']['total'] = $collection->count();
        DB::beginTransaction();
        try {
            foreach ($collection as $index => $row) {
                $rowNumber = $index + 2; // 1 header + 0-based index

                // Skip empty rows (including rows with only empty strings or null values)
                if ($row->filter(function ($value) {
                    return !is_null($value) && trim($value) !== '';
                })->isEmpty()) {
                    Log::info("OrganizationImport: Skipping empty row {$rowNumber}");
                    continue;
                }

                try {
                    $mappedRow = $this->mapFields($row->toArray()); // Map fields
                    Log::info("OrganizationImport: Processing row {$rowNumber}", [
                        'row_data' => $mappedRow
                    ]);
                    $result = $this->processRow($mappedRow, $rowNumber);
                    $this->results['details'][] = [
                        'row' => $rowNumber,
                        'name' => $mappedRow['legal_name'] ?? '',
                        'status' => $result['status'],
                        'message' => $result['message']
                    ];
                    $this->results['summary'][$result['status']]++;
                } catch (\Exception $e) {
                    $this->results['details'][] = [
                        'row' => $rowNumber,
                        'name' => $row['Legal name'] ?? '',
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                    $this->results['summary']['failed']++;
                    Log::error("OrganizationImport: Row {$rowNumber} failed: " . $e->getMessage(), [
                        'row' => $row->toArray(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("OrganizationImport: Transaction failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function startRow(): int
    {
        return 2; // 1 header row, data starts at 2
    }

    public function rules(): array
    {
        return [
            'category' => 'nullable|string|max:50', // Added max length validation for category
            'legal_name' => 'required|string|max:255|unique:organizations,legal_name',
            'organization_type' => 'nullable|string',
            'registration_number' => [
                'nullable',
                'regex:/^[0-9A-Za-z\-\/() ]+$/'
            ],
            'contact_email' => 'nullable|email',
            'contact_phone' => ['nullable', 'regex:/^[0-9+\-() ]+$/'],
            'date_established' => [
                'nullable',
                'integer',
                'between:1000,' . now()->year,
            ],

            'address' => 'nullable|string', // Updated from address_line_1 to address
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'primary_contact_name' => 'nullable|string',
            'primary_contact_email' => 'nullable|email',
            'primary_contact_phone' => ['nullable', 'regex:/^[0-9+\-() ]+$/'],
        ];
    }

    private function mapFields(array $row): array
    {
        $mappedRow = [];
        foreach ($row as $key => $value) {
            $mappedKey = $this->fieldMapping[$key] ?? $key; // Use mapping if available, otherwise keep original key
            $mappedRow[$mappedKey] = $value;
        }
        return $mappedRow;
    }

    private function processRow(array $row, int $rowNumber): array
    {
        // List of standard org fields
        $orgFields = [
            'category',
            'legal_name',
            'display_name',
            'organization_type',
            'registration_number',
            'contact_email',
            'contact_phone',
            'date_established',
            'address',
            'city',
            'country',
            'primary_contact_name',
            'primary_contact_email',
            'primary_contact_phone',
        ];

        $orgData = [];
        foreach ($orgFields as $field) {
            if (array_key_exists($field, $row)) {
                $orgData[$field] = is_string($row[$field]) ? trim($row[$field]) : $row[$field]; // Trim string fields
            }
        }

        // Check if category exceeds the maximum length
        if (!empty($orgData['category']) && strlen($orgData['category']) > 50) {
            Log::error("OrganizationImport: Category value too long at row {$rowNumber}", [
                'row_data' => $row
            ]);
            return [
                'status' => 'failed',
                'message' => "The category field exceeds the maximum length of 50 characters at row {$rowNumber}."
            ];
        }

        // Check if legal_name is missing
        if (empty($orgData['legal_name'])) {
            Log::error("OrganizationImport: Missing or empty legal_name at row {$rowNumber}", [
                'row_data' => $row
            ]);
            return [
                'status' => 'failed',
                'message' => "The legal_name field is required at row {$rowNumber} and cannot be empty."
            ];
        }

        // Auto-generate the 'code' field if not provided
        if (empty($orgData['registration_number'])) {
            Log::warning("OrganizationImport: Missing registration_number at row {$rowNumber}, generating fallback code", [
                'row_data' => $row
            ]);
            $orgData['code'] = strtoupper(substr($orgData['legal_name'], 0, 3)) . '-' . substr(md5(uniqid()), 0, 5);
        } else {
            $orgData['code'] = strtoupper(substr($orgData['legal_name'], 0, 3)) . '-' . substr(md5($orgData['registration_number']), 0, 5);
        }

        // Ensure 'country' field has a default value if missing or null
        if (empty($orgData['country'])) {
            Log::warning("OrganizationImport:
            Missing country at row {$rowNumber}, setting default value", [
                'row_data' => $row
            ]);
            $orgData['country'] = 'Uganda'; // Default country
        }

        if (isset($row['date_established'])) {
            $row['date_established'] = is_numeric($row['date_established'])
                ? (int) $row['date_established']
                : null;
        }

        $Organization = Organization::create($orgData);

        $orgData['year_established'] = $row['date_established'];

        return [
            'status' => 'success',
            'message' => 'Created organization'
        ];
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
