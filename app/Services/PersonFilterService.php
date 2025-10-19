<?php

namespace App\Services;

use App\Models\Person;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Builder;

class PersonFilterService
{
    protected $query;
    protected $filters = [];
    protected $organisation;

    public function __construct($organisation = null)
    {
        $this->organisation = $organisation;
        $this->query = Person::with([
            'phones' => fn($q) => $q->where('is_primary', true),
            'emailAddresses' => fn($q) => $q->where('is_primary', true),
            'affiliations.organisation'
        ]);
    }

    public function applyFilters(array $filters): self
    {
        $this->filters = $filters;

        foreach ($filters as $field => $value) {
            if (empty($value)) continue;

            $this->applyFilter($field, $value);
        }

        return $this;
    }

    protected function applyFilter(string $field, $value): void
    {
        switch ($field) {
            case 'search':
                $this->applySearchFilter($value);
                break;
            case 'classification':
                $this->applyClassificationFilter($value);
                break;
            case 'organisation_id':
                $this->applyOrganisationFilter($value);
                break;
            case 'age_range':
                $this->applyAgeRangeFilter($value);
                break;
            case 'gender':
                $this->applyGenderFilter($value);
                break;
            case 'status':
                $this->applyStatusFilter($value);
                break;
            case 'custom_fields':
                $this->applyCustomFieldsFilter($value);
                break;
            case 'date_range':
                $this->applyDateRangeFilter($value);
                break;
            default:
                $this->applyDynamicFilter($field, $value);
                break;
        }
    }

    protected function applySearchFilter($search): void
    {
        $this->query->where(function($q) use ($search) {
            $q->where('given_name', 'like', "%{$search}%")
              ->orWhere('family_name', 'like', "%{$search}%")
              ->orWhere('middle_name', 'like', "%{$search}%")
              ->orWhereHas('phones', function($phoneQuery) use ($search) {
                  $phoneQuery->where('number', 'like', "%{$search}%");
              })
              ->orWhereHas('emailAddresses', function($emailQuery) use ($search) {
                  $emailQuery->where('email', 'like', "%{$search}%");
              });
        });
    }

    protected function applyClassificationFilter($classification): void
    {
        if (is_array($classification)) {
            $this->query->where(function($q) use ($classification) {
                foreach ($classification as $role) {
                    $q->orWhereJsonContains('classification', $role);
                }
            });
        } else {
            $this->query->whereJsonContains('classification', $classification);
        }
    }

    protected function applyOrganisationFilter($organisationId): void
    {
        $this->query->whereHas('affiliations', function($q) use ($organisationId) {
            $q->where('organisation_id', $organisationId)
              ->where('status', 'active');
        });
    }

    protected function applyAgeRangeFilter($ageRange): void
    {
        [$minAge, $maxAge] = explode('-', $ageRange);
        $maxDate = now()->subYears($minAge)->format('Y-m-d');
        $minDate = now()->subYears($maxAge + 1)->format('Y-m-d');

        $this->query->whereBetween('date_of_birth', [$minDate, $maxDate]);
    }

    protected function applyGenderFilter($gender): void
    {
        $this->query->where('gender', $gender);
    }

    protected function applyStatusFilter($status): void
    {
        $this->query->whereHas('affiliations', function($q) use ($status) {
            $q->where('status', $status);
        });
    }

    protected function applyCustomFieldsFilter($customFields): void
    {
        foreach ($customFields as $field => $value) {
            $this->query->whereJsonContains('custom_fields->' . $field, $value);
        }
    }

    protected function applyDateRangeFilter($dateRange): void
    {
        if (is_array($dateRange) &&
            isset($dateRange['start']) && isset($dateRange['end']) &&
            !empty($dateRange['start']) && !empty($dateRange['end'])) {
            $this->query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }
    }

    protected function applyDynamicFilter($field, $value): void
    {
        // Handle dynamic fields based on organisation configuration
        if ($this->organisation) {
            $fieldConfig = $this->getOrganisationFieldConfig($field);

            if ($fieldConfig) {
                switch ($fieldConfig['type']) {
                    case 'json':
                        $this->query->whereJsonContains($fieldConfig['column'], $value);
                        break;
                    case 'relation':
                        $this->query->whereHas($fieldConfig['relation'], function($q) use ($fieldConfig, $value) {
                            $q->where($fieldConfig['field'], $value);
                        });
                        break;
                    default:
                        $this->query->where($field, $value);
                        break;
                }
            }
        }
    }

    protected function getOrganisationFieldConfig($field): ?array
    {
        // This would come from organisation-specific configuration
        if ($this->organisation && isset($this->organisation->filter_configurations)) {
            return $this->organisation->filter_configurations[$field] ?? null;
        }

        return null;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function paginate($perPage = 10)
    {
        $this->applyOrganizationConstraint();
        return $this->query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function get()
    {
        $this->applyOrganizationConstraint();
        return $this->query->orderBy('created_at', 'desc')->get();
    }

    public function count()
    {
        $this->applyOrganizationConstraint();
        return $this->query->count();
    }

    /**
     * Apply organization constraint if organization is set
     */
    public function applyOrganizationConstraint(): void
    {
        if ($this->organisation) {
            $this->query->whereHas('affiliations', function($q) {
                $q->where('organisation_id', $this->organisation->id)
                  ->where('status', 'active');
            });
        }
    }
}
