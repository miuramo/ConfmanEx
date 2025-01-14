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
            "シェファーディング採択" => 8,
            "シェファーディング対象（未採択）" => 0,
            "インタラクティブ採択（プレミアム）" => 7,
            "インタラクティブ採択（一般）" => 6,
            "登壇デモ" => 5,
            "予備1" => 1,
            "予備2" => 1,
            "予備3" => 1,
            "予備4" => 1,
            "予備5" => 1,
            "予備6" => 1,
            "予備7" => 1,
            "予備8" => 1,
            "予備9" => 1,
            "予備10" => 1,
            "予備11" => 1,
            "予備12" => 1,
            "---" => 0,
            "不採択" => -1,
            "発表取り下げ" => -2,
            "投稿不備" => -3
        ];
        foreach ($data as $d => $n) {
            \App\Models\Accept::factory()->create([
                'name' => $d,
                'shortname' => $d,
                'judge' => $n,
                'bgcolor' => ($n > 0) ? "orange" : "gray",
                'color' => ($n > 0) ? "red" : "black",
            ]);
        }
        //
    }
}
