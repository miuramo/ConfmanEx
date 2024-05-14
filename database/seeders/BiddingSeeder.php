<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BiddingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            1 => "利害by著者",
            2 => "利害by査読者",
            3 => "困難",
            4 => "可能",
            5 => "希望",
            6 => "希望by著者",
        ];
        $bg = [
            1 => "red",
            2 => "pink",
            3 => "orange",
            4 => "lime",
            5 => "cyan",
            6 => "yellow",
        ];
        foreach ($data as $n=>$d) {
            \App\Models\Bidding::factory()->create([
                'name' => $d,
                'bgcolor' => $bg[$n],
            ]);
        }
    }
}
