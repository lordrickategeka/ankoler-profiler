<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilterConfiguration extends Model
{
    protected $fillable = [
        'organisation_id',
        'field_name',
        'field_type',
        'field_options',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_active' => 'boolean'
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Get the field options for select/multiselect fields
     */
    public function getOptionsAttribute(): array
    {
        return $this->field_options['options'] ?? [];
    }

    /**
     * Get the validation rules for this field
     */
    public function getValidationRulesAttribute(): array
    {
        return $this->field_options['validation'] ?? [];
    }

    /**
     * Check if this is a select type field
     */
    public function isSelectField(): bool
    {
        return in_array($this->field_type, ['select', 'multiselect']);
    }

    /**
     * Scope to get active filters for an organisation
     */
    public function scopeActiveForOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId)
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }
}
