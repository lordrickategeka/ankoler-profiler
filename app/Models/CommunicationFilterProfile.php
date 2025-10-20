<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationFilterProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'user_id',

        'name',
        'description',
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


    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId)->where('is_active', true);
    }

    
    public function getFilterSummaryAttribute()
    {
        if (empty($this->filter_criteria)) {
            return 'No filters';
        }

        $count = count($this->filter_criteria);
        return $count . ' filter' . ($count !== 1 ? 's' : '') . ' applied';
    }

    /**
     * Check if the profile is owned by a specific user.
     */
    public function isOwnedBy($userId)
    {
        return $this->user_id === $userId;
    }

    /**
     * Increment usage count and update last used timestamp.
     */
    public function markAsUsed()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get human-readable filter criteria
     */
    public function getFormattedCriteriaAttribute()
    {
        $criteria = $this->filter_criteria;
        $formatted = [];

        foreach ($criteria as $key => $value) {
            if (!empty($value)) {
                $label = ucfirst(str_replace('_', ' ', $key));
                $formatted[] = $label . ': ' . (is_array($value) ? implode(', ', $value) : $value);
            }
        }

        return $formatted;
    }
}
