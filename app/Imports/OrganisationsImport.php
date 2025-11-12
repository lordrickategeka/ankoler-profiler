<?php

namespace App\Imports;

use App\Models\Organisation;
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
        $orgData = [
            'category' => $row['category'],
            'legal_name' => $row['legal_name'],
            'display_name' => $row['display_name'] ?? $row['legal_name'],
            'code' => $row['code'],
            'organization_type' => $row['organization_type'],
            'registration_number' => $row['registration_number'],
            'contact_email' => $row['contact_email'],
            'contact_phone' => $row['contact_phone'],
            'date_established' => $row['date_established'],
            'address_line_1' => $row['address_line_1'],
            'city' => $row['city'],
            'country' => $row['country'],
            'primary_contact_name' => $row['primary_contact_name'],
            'primary_contact_email' => $row['primary_contact_email'],
            'primary_contact_phone' => $row['primary_contact_phone'],
        ];
        Organisation::create($orgData);
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
