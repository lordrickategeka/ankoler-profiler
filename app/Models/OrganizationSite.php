<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationSite extends Model
{
    use HasFactory;

    protected $table = 'organization_sites';

    protected $fillable = [
        'organization_id',
        'site_name',
        'site_code',
        'site_type',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'site_contact_name',
        'site_contact_phone',
        'site_contact_email',
        'operating_hours_start',
        'operating_hours_end',
        'services_available',
        'site_specific_details',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'operating_hours_start' => 'datetime:H:i',
        'operating_hours_end' => 'datetime:H:i',
        'services_available' => 'array',
        'site_specific_details' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships

    /**
     * Organization that owns this site
     */
    public function Organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Accessors

    /**
     * Get the full address as a formatted string
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->address_line_1;

        if ($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }

        $address .= ', ' . $this->city;

        if ($this->district) {
            $address .= ', ' . $this->district;
        }

        if ($this->postal_code) {
            $address .= ' ' . $this->postal_code;
        }

        return $address;
    }

    /**
     * Get site type display name
     */
    public function getSiteTypeDisplayAttribute(): string
    {
        return match($this->site_type) {
            'branch' => 'Branch Office',
            'campus' => 'Campus',
            'ward' => 'Ward',
            'department' => 'Department',
            'clinic' => 'Clinic',
            'office' => 'Office',
            default => ucfirst($this->site_type)
        };
    }

    // Scopes

    /**
     * Scope to get active sites
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by site type
     */
    public function scopeBySiteType($query, string $type)
    {
        return $query->where('site_type', $type);
    }

    /**
     * Scope to search sites
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('site_name', 'like', "%{$search}%")
              ->orWhere('site_code', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('district', 'like', "%{$search}%");
        });
    }

    // Helper Methods

    /**
     * Check if site offers specific service
     */
    public function hasService(string $service): bool
    {
        return in_array($service, $this->services_available ?? []);
    }
}
