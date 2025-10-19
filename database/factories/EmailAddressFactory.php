<?php

namespace Database\Factories;

use App\Models\EmailAddress;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailAddress>
 */
class EmailAddressFactory extends Factory
{
    protected $model = EmailAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'mak.ac.ug', 'ug.edu', 'mulago.go.ug'];
        
        return [
            'person_id' => Person::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'type' => $this->faker->randomElement(['personal', 'work', 'school']),
            'is_primary' => true,
            'is_verified' => $this->faker->boolean(60),
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    /**
     * Mark as secondary email
     */
    public function secondary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => false,
                'type' => $this->faker->randomElement(['work', 'school']),
            ];
        });
    }

    /**
     * Create work email
     */
    public function work(): static
    {
        return $this->state(function (array $attributes) {
            $workDomains = ['mak.ac.ug', 'mulago.go.ug', 'mtn.com', 'company.co.ug'];
            return [
                'email' => $this->faker->unique()->userName() . '@' . $this->faker->randomElement($workDomains),
                'type' => 'work',
            ];
        });
    }
}