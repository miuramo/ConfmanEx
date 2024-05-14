<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnqueteConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\EnqueteConfig::factory()->create([
            'enquete_id' => 1,
            'orderint' => 1,
            'catcsv' => '1,2',
        ]);
        \App\Models\EnqueteConfig::factory()->create([
            'enquete_id' => 2,
            'orderint' => 2,
            'catcsv' => '0',
        ]);
        \App\Models\EnqueteConfig::factory()->create([
            'enquete_id' => 3,
            'orderint' => 3,
            'catcsv' => 'd1,d2', //
        ]);
        // \App\Models\EnqueteConfig::factory()->create([
        //     'enquete_id' => 1,
        //     'orderint' => 2,
        //     'catcsv' => '1,2',
        //     'openstart' => "4-1",
        //     'openend' => "5-1",
        // ]);
    }
}
