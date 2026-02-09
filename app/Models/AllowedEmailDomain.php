<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedEmailDomain extends Model
{
    protected $fillable = [
        'domain',
        'organization_id',
        'is_active',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
