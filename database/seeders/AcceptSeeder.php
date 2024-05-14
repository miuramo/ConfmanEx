<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "採択" => 10,
            "採択（ショート）" => 9,
            "インタラクティブ採択（プレミアム）" => 8,
            "インタラクティブ採択（一般）" => 7,
            "登壇デモ" => 6,
            "予備1" => 5,
            "予備2" => 5,
            "予備3" => 5,
            "予備4" => 5,
            "予備5" => 5,
            "予備6" => 5,
            "予備7" => 5,
            "予備8" => 5,
            "予備9" => 5,
            "予備10" => 5,
            "予備11" => 5,
            "予備12" => 5,
            "予備13" => 5,
            "予備14" => 5,
            "---" => 0,
            "不採択" => -1,
            "発表取り下げ" => -2,
            "投稿不備" => -3
        ];
        foreach ($data as $d => $n) {
            \App\Models\Accept::factory()->create([
                'name' => $d,
                'judge' => $n,
                'bgcolor' => ($n > 0) ? "orange" : "gray",
                'color' => ($n > 0) ? "red" : "black",
            ]);
        }
        //
    }
}
