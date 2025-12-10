<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use App\Models\Organization;

class PersonStandardImportTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Provide a single empty row as a template
        return new Collection([
            [
                'given_name' => '',
                'middle_name' => '',
                'family_name' => '',
                'phone_number' => '', // To phones table
                'email' => '', // To email_addresses table
                'date_of_birth' => '',
                'gender' => '',
                'address' => '',
                'country' => '',
                'city' => '',
                'district' => '',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'given_name',
            'middle_name',
            'family_name',
            'phone_number',
            'email',
            'date_of_birth',
            'gender',
            'address',
            'country',
            'city',
            'district',
        ];
    }
}
