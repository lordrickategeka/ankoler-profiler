<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrganizationTemplateExport implements FromArray, WithStyles, WithColumnFormatting
{
    protected $data;
    protected $headers;

    public function __construct(array $data = [], array $headers = [])
    {
        $this->data = $data ?: [
            [
                'school', 'Sample School', 'Sample School', 'SS001', 'STANDALONE', 'REG1234', 'school@example.com', '256700000001', '2020-01-01', '123 Main St', 'Kampala', 'UGA', 'John Doe', 'john.doe@example.com', '256700000002'
            ]
        ];
        $this->headers = $headers ?: [
            'category',
            'legal_name',
            'display_name',
            'code',
            'organization_type',
            'registration_number',
            'contact_email',
            'contact_phone',
            'date_established',
            'address_line_1',
            'city',
            'country',
            'primary_contact_name',
            'primary_contact_email',
            'primary_contact_phone',
        ];
    }

    public function array(): array
    {
        return array_merge([
            $this->headers
        ], $this->data);
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
        $sheet->getStyle('1:1')->getFont()->setBold(true);
        return [];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_TEXT, // contact_phone
            'O' => NumberFormat::FORMAT_TEXT, // primary_contact_phone
        ];
    }
}
