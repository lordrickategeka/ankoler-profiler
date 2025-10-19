<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PersonIdentifier extends Model
{
    use HasFactory;
    protected $fillable = [
        'identifier_id',
        'person_id',
        'type',
        'identifier',
        'issuing_authority',
        'issued_date',
        'expiry_date',
        'is_verified',
        'verified_at',
        'verified_by',
        'is_public',
        'visibility',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($identifier) {
            if (empty($identifier->identifier_id)) {
                $identifier->identifier_id = self::generateIdentifierId();
            }
        });
    }

    /**
     * Generate unique identifier ID
     */
    public static function generateIdentifierId(): string
    {
        $prefix = 'ID-';
        $lastIdentifier = self::where('identifier_id', 'like', $prefix . '%')
            ->orderBy('identifier_id', 'desc')
            ->first();

        if ($lastIdentifier) {
            $lastNumber = (int) substr($lastIdentifier->identifier_id, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Check if identifier is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Scope for active identifiers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for verified identifiers
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
