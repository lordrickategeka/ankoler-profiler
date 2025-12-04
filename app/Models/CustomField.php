<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $table = 'custom_fields';

    protected $fillable = [
        'model_type',
        'model_id',
        'field_name',
        'field_label',
        'field_type',
        'field_value',
        'field_options',
        'is_required',
        'validation_rules',
        'group',
        'order',
        'description',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Get the parent model (organisation, person, etc.)
     */
    public function model()
    {
        return $this->morphTo();
    }
}
