<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING RESET FILTERS FUNCTIONALITY ===\n\n";

// Test 1: Check resetFilters method exists
echo "1. CHECKING RESETFILTERS METHOD:\n";
$reflection = new ReflectionClass(\App\Livewire\Person\PersonList::class);
if ($reflection->hasMethod('resetFilters')) {
    echo "   ✅ resetFilters method exists\n";

    $method = $reflection->getMethod('resetFilters');
    echo "   ✅ Method is public: " . ($method->isPublic() ? 'Yes' : 'No') . "\n";
} else {
    echo "   ❌ resetFilters method NOT found\n";
}

// Test 2: Simulate reset functionality
echo "\n2. SIMULATING RESET FUNCTIONALITY:\n";
try {
    // Create instance
    $component = new \App\Livewire\Person\PersonList();

    // Set some filters
    $component->filters = [
        'search' => 'test search',
        'classification' => 'STAFF',
        'organisation_id' => '1',
        'age_range' => '25-35',
        'gender' => 'male',
        'status' => 'active',
        'date_range' => ['start' => '2024-01-01', 'end' => '2024-12-31']
    ];

    $component->dynamicFilters = [
        'custom_field_1' => 'test value',
        'custom_field_2' => 'another value'
    ];

    echo "   Before reset:\n";
    echo "     - Search: '{$component->filters['search']}'\n";
    echo "     - Classification: '{$component->filters['classification']}'\n";
    echo "     - Dynamic filters count: " . count($component->dynamicFilters) . "\n";

    // Call reset
    $component->resetFilters();

    echo "   After reset:\n";
    echo "     - Search: '{$component->filters['search']}'\n";
    echo "     - Classification: '{$component->filters['classification']}'\n";
    echo "     - Dynamic filters count: " . count($component->dynamicFilters) . "\n";

    // Check if properly reset
    $allEmpty = true;
    foreach ($component->filters as $key => $value) {
        if ($key === 'date_range') {
            if (!empty($value['start']) || !empty($value['end'])) {
                $allEmpty = false;
                break;
            }
        } else {
            if (!empty($value)) {
                $allEmpty = false;
                break;
            }
        }
    }

    if ($allEmpty && empty($component->dynamicFilters)) {
        echo "   ✅ Reset functionality working correctly\n";
    } else {
        echo "   ❌ Reset functionality has issues\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error testing reset: " . $e->getMessage() . "\n";
}

// Test 3: Check blade view implementation
echo "\n3. CHECKING BLADE VIEW IMPLEMENTATION:\n";
$bladeFile = 'resources/views/livewire/person/person-list.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    if (strpos($content, 'wire:click="resetFilters"') !== false) {
        echo "   ✅ Reset button found in blade view\n";
    } else {
        echo "   ❌ Reset button NOT found in blade view\n";
    }
} else {
    echo "   ❌ Blade file not found\n";
}

echo "\n=== TEST COMPLETE ===\n";
