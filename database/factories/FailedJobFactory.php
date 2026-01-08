<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FailedJob>
 */
class FailedJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'connection' => $this->faker->word(),
            'queue' => $this->faker->word(),
            'payload' => $this->faker->text(),
            'exception' => $this->faker->text(),
            'failed_at' => $this->faker->dateTime(),
            //
        ];
    }
}
