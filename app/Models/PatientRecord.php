<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PatientRecord extends Model
{
     use HasUuids;

    protected $table = 'patient_records';

    protected $fillable = [
        'affiliation_id',
        'patient_number',
        'medical_record_number',
        'primary_physician_id',
        'primary_care_unit_id',
        'allergies',
        'chronic_conditions',
        'last_visit',
        'clinical_notes',
    ];

    protected $casts = [
        'last_visit' => 'datetime',
        'clinical_notes' => 'array',
    ];

    public function affiliation()
    {
        return $this->belongsTo(PersonAffiliation::class, 'affiliation_id');
    }

    public function primaryPhysician()
    {
        return $this->belongsTo(Person::class, 'primary_physician_id');
    }

    public function primaryCareUnit()
    {
        return $this->belongsTo(Organization::class, 'primary_care_unit_id');
    }

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
     * Check if patient has allergies
     */
    public function hasAllergies()
    {
        return !empty($this->allergies);
    }

    /**
     * Get allergy list as array
     */
    public function getAllergyListAttribute()
    {
        return array_filter(array_map('trim', explode(',', $this->allergies ?? '')));
    }
}
