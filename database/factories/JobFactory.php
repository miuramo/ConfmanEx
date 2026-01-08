<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'queue' => $this->faker->word(),
            'payload' => $this->faker->text(),
            'attempts' => $this->faker->numberBetween(0, 10),
            'reserved_at' => 1,
            'available_at' => 1,
            'created_at' => 1,
            //
        ];
    }
}
