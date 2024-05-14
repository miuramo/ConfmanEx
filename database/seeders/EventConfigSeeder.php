<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\EventConfig::factory()->create([
            'event_id' => 1,
            'enquete_id' => 4,
            'orderint' => 1,
        ]);
        \App\Models\EventConfig::factory()->create([
            'event_id' => 1,
            'enquete_id' => 5,
            'orderint' => 2,
        ]);
        \App\Models\EventConfig::factory()->create([
            'event_id' => 1,
            'enquete_id' => 6,
            'orderint' => 3,
        ]);
    }
}
