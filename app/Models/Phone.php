<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Phone extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone_id',
        'person_id',
        'organisation_id',
        'number',
        'type',
        'is_primary',
        'is_verified',
        'is_public',
        'visibility',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($phone) {
            if (empty($phone->phone_id)) {
                $phone->phone_id = self::generatePhoneId();
            }
        });

        // Ensure only one primary phone per person
        static::creating(function ($phone) {
            if ($phone->is_primary) {
                self::where('person_id', $phone->person_id)
                    ->update(['is_primary' => false]);
            }
        });

        static::updating(function ($phone) {
            if ($phone->is_primary && $phone->isDirty('is_primary')) {
                self::where('person_id', $phone->person_id)
                    ->where('id', '!=', $phone->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Generate unique phone ID
     */
    public static function generatePhoneId(): string
    {
        $prefix = 'PHN-';
        $lastPhone = self::where('phone_id', 'like', $prefix . '%')
            ->orderBy('phone_id', 'desc')
            ->first();

        if ($lastPhone) {
            $lastNumber = (int) substr($lastPhone->phone_id, strlen($prefix));
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

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Format phone number for display
     */
    public function getFormattedNumberAttribute(): string
    {
        // Basic formatting - can be enhanced
        return $this->number;
    }

    /**
     * Scope for active phones
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for primary phones
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
