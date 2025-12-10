<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    /**
     * Relationships
     */

    // Category created by a user
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Category has many jobs
    public function jobs()
    {
        return $this->hasMany(OrganizationJob::class, 'category_id');
    }
}
