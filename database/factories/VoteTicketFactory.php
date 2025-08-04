<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VoteTicket>
 */
class VoteTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $token = Str::random(30);
        return [
            'email' => $token.'@example.com',
            'token' => $token,
            'token_hash' => hash('sha256', $token),
            //
        ];
    }
}
