<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationFilterProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'organisation_id',
        'filter_criteria',
        'is_active',
        'is_shared',
        'usage_count',
        'last_used_at'
    ];

    protected $casts = [
        'filter_criteria' => 'array',
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    /**
     * Relationship to user who created the profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to organization
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Get the estimated person count for this filter profile
     */
    public function getEstimatedPersonCount(): int
    {
        $filterService = new \App\Services\PersonFilterService($this->organisation);
        return $filterService->applyFilters($this->filter_criteria)->count();
    }

    /**
     * Get persons matching this filter profile
     */
    public function getFilteredPersons()
    {
        $filterService = new \App\Services\PersonFilterService($this->organisation);
        return $filterService->applyFilters($this->filter_criteria)->get();
    }

    /**
     * Increment usage count and update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope for active profiles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for shared profiles
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope for user's own profiles or shared profiles in their organization
     */
    public function scopeAccessibleBy($query, $userId, $organisationId)
    {
        return $query->where(function ($q) use ($userId, $organisationId) {
            $q->where('user_id', $userId)
              ->orWhere(function ($q2) use ($organisationId) {
                  $q2->where('organisation_id', $organisationId)
                     ->where('is_shared', true);
              });
        });
    }
}
