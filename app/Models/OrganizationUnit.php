<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganizationUnit extends Model
{
    use HasFactory;

    protected $table = 'organization_units';

    protected $fillable = [
        'organisation_id',
        'name',
        'code',
        'description',
        'parent_unit_id',
        'is_active',
    ];

    /**
     * Parent unit relationship
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_unit_id');
    }

    /**
     * Children units relationship
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_unit_id');
    }
}
