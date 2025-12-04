<?php

namespace App\Imports;

use App\Models\Organisation;
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

class OrganisationsImport implements ToCollection, WithHeadingRow, WithValidation, WithStartRow
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

    public function collection(Collection $collection)
    {
        $this->results['summary']['total'] = $collection->count();
        DB::beginTransaction();
        try {
            foreach ($collection as $index => $row) {
                $rowNumber = $index + 2; // 1 header + 0-based index
                try {
                    Log::info("OrganisationImport: Processing row {$rowNumber}", [
                        'row_data' => $row->toArray()
                    ]);
                    $result = $this->processRow($row->toArray(), $rowNumber);
                    $this->results['details'][] = [
                        'row' => $rowNumber,
                        'name' => $row['legal_name'] ?? '',
                        'status' => $result['status'],
                        'message' => $result['message']
                    ];
                    $this->results['summary'][$result['status']]++;
                } catch (\Exception $e) {
                    $this->results['details'][] = [
                        'row' => $rowNumber,
                        'name' => $row['legal_name'] ?? '',
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                    $this->results['summary']['failed']++;
                    Log::error("OrganisationImport: Row {$rowNumber} failed: " . $e->getMessage(), [
                        'row' => $row->toArray(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("OrganisationImport: Transaction failed", [
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
            'category' => 'required|string',
            'legal_name' => 'required|string|max:255|unique:organisations,legal_name',
            'code' => 'required|string|max:20|unique:organisations,code',
            'organization_type' => 'required|string',
            'registration_number' => ['required', 'regex:/^[0-9+\-() ]+$/'],
            'contact_email' => 'required|email',
            'contact_phone' => ['required', 'regex:/^[0-9+\-() ]+$/'],
            'date_established' => 'nullable|date',
            'address_line_1' => 'required|string',
            'city' => 'string',
            'country' => 'string|size:3',
            'primary_contact_name' => 'required|string',
            'primary_contact_email' => 'required|email',
            'primary_contact_phone' => ['required', 'regex:/^[0-9+\-() ]+$/'],
        ];
    }

    private function processRow(array $row, int $rowNumber): array
    {
        // List of standard org fields
        $orgFields = [
            'category', 'legal_name', 'display_name', 'code', 'organization_type',
            'registration_number', 'contact_email', 'contact_phone', 'date_established',
            'address_line_1', 'city', 'country', 'primary_contact_name',
            'primary_contact_email', 'primary_contact_phone',
        ];

        $orgData = [];
        foreach ($orgFields as $field) {
            if (array_key_exists($field, $row)) {
                $orgData[$field] = $row[$field];
            }
        }

        $organisation = Organisation::create($orgData);

        // Handle custom fields
        foreach ($row as $key => $value) {
            if (!in_array($key, $orgFields) && $value !== null && $value !== '') {
                // Find or create the custom field definition
                $customField = CustomField::firstOrCreate(
                    ['name' => $key],
                    [
                        'label' => ucwords(str_replace('_', ' ', $key)),
                        'type' => 'string', // Default type, adjust as needed
                    ]
                );
                // Save the value for this org and field
                CustomFieldValue::create([
                    'organisation_id' => $organisation->id,
                    'custom_field_id' => $customField->id,
                    'value' => $value,
                ]);
            }
        }

        return [
            'status' => 'success',
            'message' => 'Created organization with custom fields'
        ];
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
