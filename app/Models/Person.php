<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Person extends Model
{
    use HasFactory;
    protected $table = 'persons';
    protected $fillable = [
        'person_id',
        'global_identifier',
        'given_name',
        'middle_name',
        'family_name',
        'date_of_birth',
        'gender',
        'classification',
        'address',
        'city',
        'district',
        'country',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'classification' => 'array',
        'date_of_birth' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($person) {
            if (empty($person->person_id)) {
                $person->person_id = self::generatePersonId();
            }
            if (empty($person->global_identifier)) {
                $person->global_identifier = Str::uuid();
            }
        });
    }

    /**
     * Generate unique person ID
     */
    public static function generatePersonId(): string
    {
        $prefix = 'PRS-';
        $lastPerson = self::where('person_id', 'like', $prefix . '%')
            ->orderBy('person_id', 'desc')
            ->first();

        if ($lastPerson) {
            $lastNumber = (int) substr($lastPerson->person_id, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->given_name,
            $this->middle_name,
            $this->family_name
        ]);
        
        return implode(' ', $parts);
    }

    /**
     * Relationships
     */
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function emailAddresses(): HasMany
    {
        return $this->hasMany(EmailAddress::class);
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(PersonIdentifier::class);
    }

    public function affiliations(): HasMany
    {
        return $this->hasMany(PersonAffiliation::class);
    }

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'person_affiliations')
            ->withPivot([
                'affiliation_id',
                'site',
                'role_type',
                'role_title',
                'start_date',
                'end_date',
                'status',
                'domain_record_type',
                'domain_record_id',
                'permissions',
                'can_view_cross_org_data'
            ])
            ->withTimestamps();
    }

    /**
     * Domain-specific record relationships
     */
    public function patientRecords(): HasManyThrough
    {
        return $this->hasManyThrough(
            PatientRecord::class,
            PersonAffiliation::class,
            'person_id',
            'affiliation_id',
            'id',
            'id'
        );
    }

    public function staffRecords(): HasManyThrough
    {
        return $this->hasManyThrough(
            StaffRecord::class,
            PersonAffiliation::class,
            'person_id',
            'affiliation_id',
            'id',
            'id'
        );
    }

    public function studentRecords(): HasManyThrough
    {
        return $this->hasManyThrough(
            StudentRecord::class,
            PersonAffiliation::class,
            'person_id',
            'affiliation_id',
            'id',
            'id'
        );
    }

    public function saccoMemberRecords(): HasManyThrough
    {
        return $this->hasManyThrough(
            SaccoMemberRecord::class,
            PersonAffiliation::class,
            'person_id',
            'affiliation_id',
            'id',
            'id'
        );
    }

    public function parishMemberRecords(): HasManyThrough
    {
        return $this->hasManyThrough(
            ParishMemberRecord::class,
            PersonAffiliation::class,
            'person_id',
            'affiliation_id',
            'id',
            'id'
        );
    }

    /**
     * Get primary phone
     */
    public function primaryPhone()
    {
        return $this->phones()->where('is_primary', true)->first();
    }

    /**
     * Get primary email
     */
    public function primaryEmail()
    {
        return $this->emailAddresses()->where('is_primary', true)->first();
    }

    /**
     * Get national ID
     */
    public function nationalId()
    {
        return $this->identifiers()->where('type', 'national_id')->first();
    }

    /**
     * Get active affiliations
     */
    public function activeAffiliations()
    {
        return $this->affiliations()->where('status', 'active');
    }

    /**
     * Check if person has affiliation with organization
     */
    public function hasAffiliationWith($organisationId, $roleType = null): bool
    {
        $query = $this->affiliations()
            ->where('organisation_id', $organisationId)
            ->where('status', 'active');

        if ($roleType) {
            $query->where('role_type', $roleType);
        }

        return $query->exists();
    }

    /**
     * Add classification to person
     */
    public function addClassification(string $classification): void
    {
        $classifications = $this->classification ?? [];
        
        if (!in_array($classification, $classifications)) {
            $classifications[] = $classification;
            $this->classification = $classifications;
            $this->save();
        }
    }

    /**
     * Remove classification from person
     */
    public function removeClassification(string $classification): void
    {
        $classifications = $this->classification ?? [];
        $classifications = array_diff($classifications, [$classification]);
        $this->classification = array_values($classifications);
        $this->save();
    }

    /**
     * Scope for searching by name
     */
    public function scopeSearchByName($query, string $name)
    {
        $searchTerms = explode(' ', trim($name));
        
        return $query->where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->where(function ($subQ) use ($term) {
                    $subQ->where('given_name', 'like', "%{$term}%")
                         ->orWhere('middle_name', 'like', "%{$term}%")
                         ->orWhere('family_name', 'like', "%{$term}%");
                });
            }
        });
    }

    /**
     * Scope for active persons
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
