<?php

namespace Database\Factories;

use App\Models\PersonIdentifier;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonIdentifier>
 */
class PersonIdentifierFactory extends Factory
{
    protected $model = PersonIdentifier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'type' => 'national_id',
            'identifier' => $this->generateNationalId(),
            'issuing_authority' => 'National Identification and Registration Authority (NIRA)',
            'issued_date' => $this->faker->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
            'expiry_date' => null,
            'is_verified' => $this->faker->boolean(70),
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    /**
     * Generate a realistic Ugandan National ID
     */
    private function generateNationalId(): string
    {
        // Uganda National ID format: CM + 8 digits + 5 digits + 3 digits
        return 'CM' . $this->faker->numerify('########') . $this->faker->numerify('#####') . $this->faker->numerify('###');
    }

    /**
     * Create passport identifier
     */
    public function passport(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'passport',
                'identifier' => 'B' . $this->faker->numerify('#######'),
                'issuing_authority' => 'Ministry of Internal Affairs - Uganda',
                'issued_date' => $this->faker->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
                'expiry_date' => $this->faker->dateTimeBetween('+1 year', '+10 years')->format('Y-m-d'),
            ];
        });
    }

    /**
     * Create driver's license identifier
     */
    public function driversLicense(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'drivers_license',
                'identifier' => 'DL' . $this->faker->numerify('######'),
                'issuing_authority' => 'Ministry of Works and Transport - Uganda',
                'issued_date' => $this->faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
                'expiry_date' => $this->faker->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            ];
        });
    }

    /**
     * Create professional license identifier
     */
    public function professionalLicense(): static
    {
        return $this->state(function (array $attributes) {
            $licenses = [
                ['type' => 'medical_license', 'authority' => 'Uganda Medical and Dental Practitioners Council'],
                ['type' => 'teaching_license', 'authority' => 'Ministry of Education and Sports'],
                ['type' => 'legal_license', 'authority' => 'Uganda Law Society'],
                ['type' => 'nursing_license', 'authority' => 'Uganda Nurses and Midwives Council'],
            ];
            
            $license = $this->faker->randomElement($licenses);
            
            return [
                'type' => 'professional_license',
                'identifier' => strtoupper(substr($license['type'], 0, 3)) . $this->faker->numerify('####'),
                'issuing_authority' => $license['authority'],
                'issued_date' => $this->faker->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
                'expiry_date' => $this->faker->dateTimeBetween('+1 year', '+3 years')->format('Y-m-d'),
            ];
        });
    }
}