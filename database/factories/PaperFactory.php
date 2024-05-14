<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PaperFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'owner' => 1,
            'category_id' => 2,
            'contactemails' => $this->faker->safeEmail(). "\n"
            . $this->faker->safeEmail() . "\n"
            . $this->faker->safeEmail() . "\n",
        ];
    }

    public function cat($cid)
    {
        return $this->state(function (array $attributes) use ($cid){
            return [
                'category_id' => $cid,
            ];
        });
    }

    public function owner($uid)
    {
        return $this->state(function (array $attributes) use ($uid){
            return [
                'owner' => $uid,
            ];
        });
    }
    public function coauthor($uid)
    {
        return $this->state(function (array $attributes) use ($uid){
            $u = User::find($uid);
            return [
                'contactemails' => $u->email,
            ];
        });
    }

    public function contactemails($emarray)
    {
        $em = implode("\n", $emarray);
        return $this->state(function (array $attributes) use ($em){
            return [
                'contactemails' => $em,
            ];
        });
    }
}
