<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailAddress extends Model
{
    use HasFactory;
    protected $fillable = [
        'email_id',
        'person_id',
        'organisation_id',
        'email',
        'type',
        'is_primary',
        'is_verified',
        'verified_at',
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
        'verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($email) {
            if (empty($email->email_id)) {
                $email->email_id = self::generateEmailId();
            }
            
            // Normalize email to lowercase
            $email->email = strtolower($email->email);
        });

        static::updating(function ($email) {
            // Normalize email to lowercase
            $email->email = strtolower($email->email);
        });

        // Ensure only one primary email per person
        static::creating(function ($email) {
            if ($email->is_primary) {
                self::where('person_id', $email->person_id)
                    ->update(['is_primary' => false]);
            }
        });

        static::updating(function ($email) {
            if ($email->is_primary && $email->isDirty('is_primary')) {
                self::where('person_id', $email->person_id)
                    ->where('id', '!=', $email->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Generate unique email ID
     */
    public static function generateEmailId(): string
    {
        $prefix = 'EML-';
        $lastEmail = self::where('email_id', 'like', $prefix . '%')
            ->orderBy('email_id', 'desc')
            ->first();

        if ($lastEmail) {
            $lastNumber = (int) substr($lastEmail->email_id, strlen($prefix));
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
     * Scope for active emails
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for primary emails
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for verified emails
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
