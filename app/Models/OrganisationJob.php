<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganisationJob extends Model
{
     use HasFactory;
    protected $fillable = [
        'organization_id',
        'category_id',
        'posted_by',
        'title',
        'description',
        'job_type',
        'location',
        'salary_range',
        'experience_level',
        'qualifications',
        'application_deadline',
        'status',
    ];

    /**
     * Relationships
     */

    // Job belongs to an organization
    public function organization()
    {
        return $this->belongsTo(Organisation::class);
    }

    // Job belongs to a category
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    // Job is posted by a user
    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Job has many applications
    // public function applications()
    // {
    //     return $this->hasMany(JobApplication::class);
    // }
}
