<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Organisation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organisations';

    protected $fillable = [
        // Basic Information
        'legal_name',
        'display_name',
        'code',
        'organisation_type',
        'parent_organisation_id',
        'registration_number',
        'tax_identification_number',
        'country_of_registration',
        'date_established',
        'logo_path',
        'website_url',
        'contact_email',
        'contact_phone',
        'description',
        'is_active',
        'category',

        // Address
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'postal_code',
        'country',
        'latitude',
        'longitude',

        // Regulatory & Compliance
        'regulatory_body',
        'license_number',
        'license_issue_date',
        'license_expiry_date',
        'accreditation_status',
        'compliance_certifications',

        // Contact Persons
        'primary_contact_name',
        'primary_contact_title',
        'primary_contact_email',
        'primary_contact_phone',
        'secondary_contact_name',
        'secondary_contact_email',
        'secondary_contact_phone',

        // Financial Information
        'bank_name',
        'bank_account_number',
        'bank_branch',
        'swift_bic_code',
        'default_currency',
        'fiscal_year_start_month',

        // System Configuration
        'timezone',
        'default_language',
        'working_days',
        'operating_hours_start',
        'operating_hours_end',

        // Category-specific details
        'hospital_details',
        'school_details',
        'sacco_details',
        'parish_details',
        'corporate_details',

        // Multi-site support
        'is_multi_site',
        'is_head_office',

        // Integration & System Settings
        'integration_settings',

        // Subscription & Billing
        'subscription_plan',
        'billing_cycle',
        'subscription_start_date',
        'subscription_end_date',
        'is_trial',
        'trial_days_remaining',

        // Documents & Metadata
        'documents',
        'metadata',
        'verified_at',
        'verified_by',
        'verification_notes',
        'is_super',
    ];

    protected $casts = [
        'date_established' => 'date',
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
        'verified_at' => 'datetime',
        'operating_hours_start' => 'datetime:H:i',
        'operating_hours_end' => 'datetime:H:i',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'is_multi_site' => 'boolean',
        'is_head_office' => 'boolean',
        'is_super' => 'boolean',
        'is_trial' => 'boolean',
        'trial_days_remaining' => 'integer',
        'fiscal_year_start_month' => 'integer',

        // JSON fields
        'compliance_certifications' => 'array',
        'working_days' => 'array',
        'hospital_details' => 'array',
        'school_details' => 'array',
        'sacco_details' => 'array',
        'parish_details' => 'array',
        'corporate_details' => 'array',
        'integration_settings' => 'array',
        'documents' => 'array',
        'metadata' => 'array',
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Relationships

    /**
     * Parent organisation relationship
     */
    public function parentorganisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'parent_organisation_id');
    }

    /**
     * Child organisations relationship
     */
    public function childorganisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'parent_organisation_id');
    }

    /**
     * organisation sites relationship
     */
    public function sites(): HasMany
    {
        return $this->hasMany(OrganisationSite::class);
    }

    /**
     * Active sites relationship
     */
    public function activeSites(): HasMany
    {
        return $this->sites()->where('is_active', true);
    }

    public function affiliations()
    {
        return $this->hasMany(\App\Models\PersonAffiliation::class, 'organisation_id');
    }


    /**
     * User who verified this organisation
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Filter configurations for this organisation
     */
    public function filterConfigurations(): HasMany
    {
        return $this->hasMany(FilterConfiguration::class);
    }

    // Accessors & Mutators

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
     * Get the display name or fall back to legal name
     */
    public function getNameAttribute(): string
    {
        return $this->display_name ?: $this->legal_name;
    }

    /**
     * Check if organisation is verified
     */
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Check if license is expired
     */
    public function getIsLicenseExpiredAttribute(): bool
    {
        if (!$this->license_expiry_date) {
            return false;
        }

        return Carbon::parse($this->license_expiry_date)->isPast();
    }

    /**
     * Get days until license expires
     */
    public function getLicenseExpiresInDaysAttribute(): ?int
    {
        if (!$this->license_expiry_date) {
            return null;
        }

        return Carbon::now()->diffInDays(Carbon::parse($this->license_expiry_date), false);
    }

    /**
     * Check if subscription is active
     */
    public function getIsSubscriptionActiveAttribute(): bool
    {
        if ($this->is_trial) {
            return $this->trial_days_remaining > 0;
        }

        if (!$this->subscription_end_date) {
            return false;
        }

        return Carbon::parse($this->subscription_end_date)->isFuture();
    }

    // Scopes

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get active organisations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get verified organisations
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope to get organisations with expiring licenses
     */
    public function scopeLicenseExpiringIn($query, int $days = 30)
    {
        return $query->whereNotNull('license_expiry_date')
                    ->whereBetween('license_expiry_date', [
                        Carbon::now(),
                        Carbon::now()->addDays($days)
                    ]);
    }

    /**
     * Scope to get organisations by country
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to search organisations
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('legal_name', 'like', "%{$search}%")
              ->orWhere('display_name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('registration_number', 'like', "%{$search}%")
              ->orWhere('contact_email', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('district', 'like', "%{$search}%");
        });
    }

    // Helper Methods

    /**
     * Get category-specific details based on organisation category
     */
    public function getCategoryDetails(): ?array
    {
        return match($this->category) {
            'hospital' => $this->hospital_details,
            'school' => $this->school_details,
            'sacco' => $this->sacco_details,
            'parish' => $this->parish_details,
            'corporate' => $this->corporate_details,
            default => null
        };
    }

    /**
     * Set category-specific details
     */
    public function setCategoryDetails(array $details): void
    {
        match($this->category) {
            'hospital' => $this->hospital_details = $details,
            'school' => $this->school_details = $details,
            'sacco' => $this->sacco_details = $details,
            'parish' => $this->parish_details = $details,
            'corporate' => $this->corporate_details = $details,
            default => null
        };
    }

    /**
     * Get organisation type badge color
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->category) {
            'hospital' => 'badge-error',
            'school' => 'badge-info',
            'sacco' => 'badge-success',
            'parish' => 'badge-warning',
            'corporate' => 'badge-primary',
            'government' => 'badge-secondary',
            'ngo' => 'badge-accent',
            default => 'badge-neutral'
        };
    }

    /**
     * Get organisation category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return match($this->category) {
            'hospital' => 'Hospital/Health Facility',
            'school' => 'School/Educational Institution',
            'sacco' => 'SACCO/Financial Cooperative',
            'parish' => 'Parish/Religious organisation',
            'corporate' => 'Corporate/Business',
            'government' => 'Government Agency',
            'ngo' => 'NGO/Non-Profit',
            default => 'Other'
        };
    }

    /**
     * Check if organisation has specific feature/service
     */
    public function hasFeature(string $feature): bool
    {
        $details = $this->getCategoryDetails();

        if (!$details) {
            return false;
        }

        return match($this->category) {
            'hospital' => $details['has_' . $feature] ?? false,
            'school' => $details['facilities']['has_' . $feature] ?? false,
            'sacco' => $details['services'][$feature] ?? false,
            default => false
        };
    }

    /**
     * Get formatted contact information
     */
    public function getContactInfoAttribute(): array
    {
        return [
            'primary' => [
                'name' => $this->primary_contact_name,
                'title' => $this->primary_contact_title,
                'email' => $this->primary_contact_email,
                'phone' => $this->primary_contact_phone,
            ],
            'secondary' => [
                'name' => $this->secondary_contact_name,
                'email' => $this->secondary_contact_email,
                'phone' => $this->secondary_contact_phone,
            ]
        ];
    }
}
