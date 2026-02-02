<?php

namespace App\Livewire\Organizations;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrganizationTemplateExport;
use App\Models\CustomField;
use App\Models\Organization;

class ImportOrganizations extends Component
{
    use WithFileUploads;

    public $file;
    public $message;

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,csv',
    ];

    public function import()
    {
        $this->validate();

        // Store uploaded file temporarily
        $path = $this->file->store('imports');

        // Use Laravel Excel to read the file
        $importedRows = [];
        \Maatwebsite\Excel\Facades\Excel::import(new class($importedRows) implements \Maatwebsite\Excel\Concerns\ToArray {
            public $rows;
            public function __construct(&$rows) { $this->rows = &$rows; }
            public function array(array $array) { $this->rows = $array; }
        }, $path);

        // Get headers from first row
        $headers = $importedRows[0] ?? [];
        unset($importedRows[0]);

        foreach ($importedRows as $row) {
            $data = array_combine($headers, $row);
            $standardFields = [];
            foreach ($data as $key => $value) {
                if (in_array($key, (new Organization)->getFillable())) {
                    $standardFields[$key] = $value;
                }
            }
            $org = new Organization($standardFields);
            $org->save();
        }

        $this->message = 'File imported successfully.';
    }

    public function exportCustomTemplate($fields)
    {
        $headers = array_map('trim', $fields);

        // Save custom fields to custom_fields table
        foreach ($fields as $field) {
            CustomField::updateOrCreate([
                'model_type' => 'Organization_template',
                'model_id' => 0,
                'field_name' => $field,
            ], [
                'field_label' => ucfirst(str_replace('_', ' ', $field)),
                'field_type' => 'string',
                'field_options' => null,
                'is_required' => false,
                'validation_rules' => null,
                'group' => null,
                'order' => null,
                'description' => null,
            ]);
        }

        $export = new OrganizationTemplateExport([], $headers);
        return Excel::download($export, 'custom_Organization_template.xlsx');
    }

    public function render()
    {
        return view('livewire.organizations.import-organizations');
    }
}
