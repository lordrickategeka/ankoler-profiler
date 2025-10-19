<?php

namespace Database\Factories;

use App\Models\Phone;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phone>
 */
class PhoneFactory extends Factory
{
    protected $model = Phone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providers = ['MTN', 'Airtel', 'Africell'];
        $prefixes = [
            'MTN' => ['701', '702', '703', '704', '706'],
            'Airtel' => ['750', '751', '752', '753', '754', '755', '756'],
            'Africell' => ['771', '772', '773', '774', '775', '776', '777']
        ];
        
        $provider = $this->faker->randomElement($providers);
        $prefix = $this->faker->randomElement($prefixes[$provider]);
        $number = $prefix . $this->faker->numerify('######');

        return [
            'person_id' => Person::factory(),
            'number' => '+256' . $number,
            'type' => $this->faker->randomElement(['mobile', 'home', 'work']),
            'is_primary' => true,
            'is_verified' => $this->faker->boolean(80),
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    /**
     * Mark as secondary phone
     */
    public function secondary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => false,
                'type' => $this->faker->randomElement(['work', 'home']),
            ];
        });
    }
}