<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            1 => "登壇発表",
            2 => "デモポスター発表",
            3 => "予備",
        ];
        $bg = [
            1 => "teal",
            2 => "lime",
            3 => "yellow",
        ];
        $fg = [
            1 => "blue",
            2 => "green",
            3 => "orange",
        ];
        $oe = [
            1 => "05-31",
            2 => "05-31",
            3 => "03-02",
        ];
        foreach ($data as $n => $d) {
            \App\Models\Category::factory()->create([
                'name' => $d,
                'shortname' => str_replace("発表","",$d),
                'bgcolor' => $bg[$n],
                'color' => $fg[$n],
                'openend' => $oe[$n],
            ]);
        }
    }
}
