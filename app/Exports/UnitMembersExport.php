<?php

namespace App\Exports;

use App\Models\PersonAffiliation;
use App\Models\OrganizationUnit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnitMembersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $unitId;

    public function __construct($unitId)
    {
        $this->unitId = $unitId;
    }

    public function collection()
    {
        return PersonAffiliation::with('person')
            ->where('domain_record_type', 'unit')
            ->where('domain_record_id', $this->unitId)
            ->whereIn('status', ['active', 'approved'])
            ->get();
    }

    public function map($affiliation): array
    {
        return [
            $affiliation->person->id ?? '',
            $affiliation->person->full_name ?? '',
            $affiliation->person->email ?? '',
            $affiliation->role_type,
            $affiliation->status,
        ];
    }

    public function headings(): array
    {
        return [
            'Person ID',
            'Full Name',
            'Email',
            'Role Type',
            'Status',
        ];
    }
}
