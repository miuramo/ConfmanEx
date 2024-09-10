<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViewpointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach([1,2,3] as $cat){
            \App\Models\Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 1,
                'name' => 'score',
                'desc' => '総合点',
                'content' => "5：採択、4：採択に近い、3：中立、2：不採択に近い、1：不採択\n; number ; 1 ; 5 ",
                'weight' => 1,
                'doReturn' => true,
                // 'contentafter' => '',
            ]);
            \App\Models\Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 2,
                'name' => 'comment',
                'desc' => '査読コメント',
                'content' => "査読コメントは著者に返ります。\n; textarea ; 60 ; 5 ; （著者に返ります）",
                'doReturn' => true,
            ]);
            \App\Models\Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 3,
                'name' => 'suisen',
                'desc' => '論文賞/デモポスター賞推薦',
                'content' => "数字が大きい(5)ほうが「推薦したい」になります。\n; number ; 1 ; 5 ",
            ]);
            \App\Models\Viewpoint::factory()->create([
                'category_id' => $cat,
                'orderint' => 4,
                'name' => 'suisencom',
                'desc' => '推薦コメント',
                'content' => "推薦コメントは委員会向けです。\n;textarea ; 60 ; 5 ; (委員会向け) ",
            ]);
        }
        //
    }
}
