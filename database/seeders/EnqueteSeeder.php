<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnqueteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Enquete::factory()->create([
            'name' => '発表カテゴリ・主要テーマ',
            'showonpaperindex' => true,
            'showonreviewerindex' => true,
        ]);

        \App\Models\Enquete::factory()->create([
            'name' => '採択時デモ',
            'showonpaperindex' => true,
            'showonreviewerindex' => false,
        ]);

        \App\Models\Enquete::factory()->create([
            'name' => 'デモ機材調査',
            'showonpaperindex' => false,
            'showonreviewerindex' => false,
        ]);

        // 4
        \App\Models\Enquete::factory()->create([
            'name' => '参加登録連絡先',
            'showonpaperindex' => true,
            'showonreviewerindex' => false,
        ]);
        // 5
        \App\Models\Enquete::factory()->create([
            'name' => '参加登録会員区分',
            'showonpaperindex' => true,
            'showonreviewerindex' => false,
        ]);
        // 6
        \App\Models\Enquete::factory()->create([
            'name' => '送迎バス希望',
            'showonpaperindex' => true,
            'showonreviewerindex' => false,
        ]);
    }
}
