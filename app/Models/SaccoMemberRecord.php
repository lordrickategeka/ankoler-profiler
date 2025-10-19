<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SaccoMemberRecord extends Model
{
    use HasUuids;

    protected $table = 'sacco_member_records';

    protected $fillable = [
        'affiliation_id',
        'membership_number',
        'join_date',
        'share_capital',
        'savings_account_ref',
        'sacco_notes',
    ];

    protected $casts = [
        'join_date' => 'date',
        'share_capital' => 'decimal:2',
        'sacco_notes' => 'array',
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
     * Get total savings
     */
    public function getTotalSavingsAttribute()
    {
        return $this->sacco_notes['total_savings'] ?? 0;
    }

    /**
     * Get active loans
     */
    public function getActiveLoansAttribute()
    {
        return $this->sacco_notes['active_loans'] ?? [];
    }

    /**
     * Get loan limit
     */
    public function getLoanLimitAttribute()
    {
        return $this->sacco_notes['loan_limit'] ?? 0;
    }
}
