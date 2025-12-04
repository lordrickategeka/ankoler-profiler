<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $table = 'custom_field_values';

    protected $fillable = [
        'organisation_id',
        'custom_field_id',
        'value',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}
