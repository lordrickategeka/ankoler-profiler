<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PersonTemplateExport implements FromArray, WithStyles, WithColumnFormatting
{
    protected $data;
    protected $headers;

    public function __construct(array $data, array $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Auto-size columns - use coordinate conversion for multi-character column names
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($i = 1; $i <= $highestColumnIndex; $i++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // Style the header row
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'E3F2FD'],
                ],
            ],
            2 => [
                'font' => [
                    'italic' => true,
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'F5F5F5'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        $formats = [];
        $columnIndex = 'A';

        foreach (array_keys($this->headers) as $header) {
            // Format phone number columns as text
            if (in_array($header, ['phone', 'guardian_phone', 'emergency_contact_phone', 'next_of_kin_phone'])) {
                $formats[$columnIndex] = NumberFormat::FORMAT_TEXT;
            }
            // Format date columns
            elseif (in_array($header, ['date_of_birth', 'join_date', 'enrollment_date', 'hire_date', 'baptism_date', 'confirmation_date', 'ordination_date'])) {
                $formats[$columnIndex] = NumberFormat::FORMAT_DATE_YYYYMMDD2;
            }
            // Format monetary columns
            elseif (in_array($header, ['share_capital', 'salary', 'monthly_income'])) {
                $formats[$columnIndex] = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
            }
            // Format ID columns as text
            elseif (in_array($header, ['national_id', 'employee_number', 'student_number', 'patient_number', 'membership_number', 'member_number'])) {
                $formats[$columnIndex] = NumberFormat::FORMAT_TEXT;
            }

            $columnIndex++;
        }

        return $formats;
    }
}
