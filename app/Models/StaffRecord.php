<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StaffRecord extends Model
{
    use HasUuids;

    protected $table = 'staff_records';

    protected $fillable = [
        'affiliation_id',
        'staff_number',
        'payroll_id',
        'employment_type',
        'grade',
        'contract_start',
        'contract_end',
        'supervisor_id',
        'work_schedule',
        'hr_notes',
    ];

    protected $casts = [
        'contract_start' => 'date',
        'contract_end' => 'date',
        'work_schedule' => 'array',
        'hr_notes' => 'array',
    ];

    /**
     * Get the affiliation for this staff record
     */
    public function affiliation()
    {
        return $this->belongsTo(PersonAffiliation::class, 'affiliation_id');
    }

    /**
     * Get the supervisor (person)
     */
    public function supervisor()
    {
        return $this->belongsTo(Person::class, 'supervisor_id');
    }

    /**
     * Get the person via affiliation
     */
    public function person()
    {
        return $this->hasOneThrough(
            Person::class,
            PersonAffiliation::class,
            'id',
            'id',
            'affiliation_id',
            'person_id'
        );
    }

    /**
     * Check if contract is active
     */
    public function isContractActive()
    {
        if (!$this->contract_end) {
            return true; // Permanent contract
        }

        return $this->contract_end->isFuture();
    }

    /**
     * Check if on probation
     */
    public function isOnProbation()
    {
        if (!isset($this->hr_notes['probation_end'])) {
            return false;
        }

        return now()->lt($this->hr_notes['probation_end']);
    }
}
