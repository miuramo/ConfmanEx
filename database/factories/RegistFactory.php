<?php

namespace Database\Factories;

use App\Models\Regist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Regist::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'valid' => $this->faker->boolean,
            'paid' => $this->faker->boolean,
            'paid_at' => $this->faker->optional()->dateTime,
            'payment_method' => $this->faker->optional()->word,
            'payment_id' => $this->faker->optional()->uuid,
            'payment_status' => $this->faker->optional()->word,
            'confirmed_at' => $this->faker->optional()->dateTime,
            'isearly' => $this->faker->boolean,
            'submitted_at' => $this->faker->dateTime,
        ];
    }
}