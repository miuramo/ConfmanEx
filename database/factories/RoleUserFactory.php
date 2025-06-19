<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsRoleUser>
 */
class RoleUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => 1, // Default role ID
            'user_id' => 1, // Default user ID
            'created_at' => now(),
            'updated_at' => now(),
            'mailnotify' => true, // Default mail notification setting
            //
        ];
    }
}
