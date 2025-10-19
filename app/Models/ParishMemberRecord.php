<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ParishMemberRecord extends Model
{
    use HasUuids;

    protected $table = 'parish_member_records';

    protected $fillable = [
        'affiliation_id',
        'member_number',
        'communion_status',
        'baptism_date',
        'parish_notes',
    ];

    protected $casts = [
        'baptism_date' => 'date',
        'parish_notes' => 'array',
    ];

    public function affiliation()
    {
        return $this->belongsTo(PersonAffiliation::class, 'affiliation_id');
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
     * Check if confirmed
     */
    public function isConfirmed()
    {
        return $this->communion_status === 'CONFIRMED';
    }

    /**
     * Get ministries
     */
    public function getMinistriesAttribute()
    {
        return $this->parish_notes['ministries'] ?? [];
    }
}
