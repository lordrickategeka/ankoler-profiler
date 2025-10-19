# Sophisticated Filtering Module Documentation

## Overview

The sophisticated filtering module provides a flexible, organization-specific filtering system for person data. It supports both static filters (common across all organizations) and dynamic filters (configurable per organization).

## Architecture

### 1. Core Components

#### PersonFilterService (`app/Services/PersonFilterService.php`)
- Main service class that handles all filtering logic
- Supports multiple filter types: search, classification, age range, gender, status, date range, and custom fields
- Automatically applies organization-based filtering when needed

#### FilterConfiguration Model (`app/Models/FilterConfiguration.php`)
- Stores organization-specific filter configurations
- Supports multiple field types: text, select, multiselect, date, number, boolean
- Includes validation rules and display options

#### PersonList Livewire Component (`app/Livewire/Person/PersonList.php`)
- Enhanced with sophisticated filtering capabilities
- Supports both basic and advanced filters
- Dynamically loads organization-specific filters

### 2. Database Schema

The `filter_configurations` table stores:
- `organisation_id`: Links to organization
- `field_name`: Unique field identifier
- `field_type`: Type of filter (text, select, etc.)
- `field_options`: JSON field storing options and validation rules
- `is_active`: Whether the filter is currently active
- `sort_order`: Display order in the UI

## Features

### Static Filters (Available for all organizations)
1. **Search**: Full-text search across name, phone, and email
2. **Classification**: Filter by person roles (STAFF, MEMBER, PATIENT, etc.)
3. **Organization**: Filter by organization (for super admins)
4. **Gender**: Filter by gender options
5. **Age Range**: Filter by predefined age ranges
6. **Status**: Filter by affiliation status
7. **Date Range**: Filter by creation date range

### Dynamic Filters (Organization-specific)
- Configurable through the admin interface
- Support multiple field types
- Can include custom validation rules
- Organization-specific options and values

### Field Types Supported
1. **Text**: Free text input
2. **Select**: Single selection from predefined options
3. **Multiselect**: Multiple selections from predefined options
4. **Date**: Date picker input
5. **Number**: Numeric input with validation
6. **Boolean**: True/false checkbox

## Usage Examples

### Basic Usage in Livewire Component

```php
// Initialize the filter service
$filterService = new PersonFilterService($currentOrganization);

// Apply filters
$allFilters = array_merge($this->filters, $this->dynamicFilters);
$persons = $filterService->applyFilters($allFilters)->paginate(10);
```

### Adding Custom Filters

```php
// Create a new filter configuration
FilterConfiguration::create([
    'organisation_id' => $org->id,
    'field_name' => 'department',
    'field_type' => 'select',
    'field_options' => [
        'options' => ['HR', 'Finance', 'IT', 'Operations'],
        'validation' => ['string', 'max:255']
    ],
    'is_active' => true,
    'sort_order' => 1
]);
```

### Filtering in the Service

```php
// The service automatically handles different filter types
$filterService = new PersonFilterService($organisation);

$filters = [
    'search' => 'john',
    'gender' => 'male',
    'age_range' => '25-35',
    'department' => 'IT' // Dynamic filter
];

$results = $filterService->applyFilters($filters)->get();
```

## Organization-Specific Examples

### Healthcare Organization
```php
// Medical specialization filter
[
    'field_name' => 'medical_specialization',
    'field_type' => 'select',
    'field_options' => [
        'options' => ['General Medicine', 'Pediatrics', 'Surgery', 'Cardiology']
    ]
]
```

### Educational Institution
```php
// Subject area filter
[
    'field_name' => 'subject_area',
    'field_type' => 'select',
    'field_options' => [
        'options' => ['Mathematics', 'Science', 'English', 'History']
    ]
]

// Grade level filter
[
    'field_name' => 'grade_level',
    'field_type' => 'select',
    'field_options' => [
        'options' => ['Primary', 'Secondary', 'Higher Education']
    ]
]
```

### Financial Institution
```php
// Account type filter
[
    'field_name' => 'account_type',
    'field_type' => 'select',
    'field_options' => [
        'options' => ['Savings', 'Current', 'Fixed Deposit', 'Loan']
    ]
]
```

## Admin Interface

### Managing Filter Configurations
- Access through `/admin/filter-configurations`
- Create, edit, delete, and toggle filter configurations
- Organization-specific access control
- Sort order management

### Controller Methods
- `index()`: List all filters for current organization
- `create()`: Show create filter form
- `store()`: Save new filter configuration
- `edit()`: Show edit filter form
- `update()`: Update existing filter
- `destroy()`: Delete filter configuration
- `toggle()`: Toggle active status

## UI Components

### Basic Filter Row
- Always visible
- Includes search, classification, and organization filters
- Responsive grid layout

### Advanced Filters
- Collapsible section
- Includes gender, age range, status, date range filters
- Dynamic organization-specific filters
- Filter count display and reset functionality

### Filter Actions
- Active filter count display
- Reset all filters button
- Show/hide advanced filters toggle

## Best Practices

### 1. Performance Optimization
- Use database indexes on filtered fields
- Implement pagination for large datasets
- Consider caching frequently used filter combinations

### 2. User Experience
- Provide clear labels and help text
- Use appropriate input types for different data
- Show filter count and active filters
- Implement filter persistence across page loads

### 3. Data Validation
- Validate filter inputs on both client and server side
- Provide meaningful error messages
- Sanitize input to prevent SQL injection

### 4. Extensibility
- Design filters to be easily extendable
- Use configuration-driven approach
- Implement proper abstractions for new filter types

## Testing

The module includes comprehensive tests covering:
- Individual filter functionality
- Combined filter scenarios
- Edge cases and error handling
- Performance with large datasets

Run tests with:
```bash
php artisan test --filter PersonFilterTest
```

## Migration and Setup

1. Run the migration:
```bash
php artisan migrate
```

2. Seed sample filter configurations:
```bash
php artisan db:seed --class=FilterConfigurationSeeder
```

3. Configure organization-specific filters through the admin interface

## Future Enhancements

1. **Advanced Filter Types**
   - Range filters (min/max values)
   - Multi-select with AND/OR logic
   - Hierarchical filters (parent/child relationships)

2. **Filter Presets**
   - Save commonly used filter combinations
   - Share filter presets between users
   - Quick filter buttons

3. **Export Functionality**
   - Export filtered results
   - Include filter criteria in export metadata
   - Support multiple export formats

4. **Analytics**
   - Track most used filters
   - Performance metrics for filter combinations
   - User behavior analytics
