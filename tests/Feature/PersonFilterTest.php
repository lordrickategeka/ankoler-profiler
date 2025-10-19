<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Person;
use App\Models\Organisation;
use App\Models\FilterConfiguration;
use App\Services\PersonFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonFilterTest extends TestCase
{
    use RefreshDatabase;

    protected $organisation;
    protected $filterService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create([
            'legal_name' => 'Test Organisation',
            'category' => 'hospital'
        ]);

        $this->filterService = new PersonFilterService($this->organisation);
    }

    public function test_can_filter_by_search_term()
    {
        // Create test persons
        $person1 = Person::factory()->create(['given_name' => 'John', 'family_name' => 'Doe']);
        $person2 = Person::factory()->create(['given_name' => 'Jane', 'family_name' => 'Smith']);

        // Filter by search term
        $results = $this->filterService->applyFilters(['search' => 'John'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John', $results->first()->given_name);
    }

    public function test_can_filter_by_classification()
    {
        // Create test persons with different classifications
        $person1 = Person::factory()->create(['classification' => ['STAFF']]);
        $person2 = Person::factory()->create(['classification' => ['MEMBER']]);

        // Filter by classification
        $results = $this->filterService->applyFilters(['classification' => 'STAFF'])->get();

        $this->assertCount(1, $results);
        $this->assertContains('STAFF', $results->first()->classification);
    }

    public function test_can_filter_by_gender()
    {
        // Create test persons with different genders
        $person1 = Person::factory()->create(['gender' => 'male']);
        $person2 = Person::factory()->create(['gender' => 'female']);

        // Filter by gender
        $results = $this->filterService->applyFilters(['gender' => 'male'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('male', $results->first()->gender);
    }

    public function test_can_filter_by_age_range()
    {
        // Create test persons with different birth dates
        $person1 = Person::factory()->create(['date_of_birth' => now()->subYears(25)->format('Y-m-d')]);
        $person2 = Person::factory()->create(['date_of_birth' => now()->subYears(45)->format('Y-m-d')]);

        // Filter by age range
        $results = $this->filterService->applyFilters(['age_range' => '18-30'])->get();

        $this->assertCount(1, $results);
    }

    public function test_can_combine_multiple_filters()
    {
        // Create test persons
        $person1 = Person::factory()->create([
            'given_name' => 'John',
            'gender' => 'male',
            'classification' => ['STAFF']
        ]);

        $person2 = Person::factory()->create([
            'given_name' => 'Jane',
            'gender' => 'female',
            'classification' => ['MEMBER']
        ]);

        // Apply multiple filters
        $results = $this->filterService->applyFilters([
            'search' => 'John',
            'gender' => 'male',
            'classification' => 'STAFF'
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John', $results->first()->given_name);
    }

    public function test_returns_empty_when_no_matches()
    {
        // Create test person
        Person::factory()->create(['given_name' => 'John', 'family_name' => 'Doe']);

        // Filter with non-matching criteria
        $results = $this->filterService->applyFilters(['search' => 'NonExistent'])->get();

        $this->assertCount(0, $results);
    }
}
