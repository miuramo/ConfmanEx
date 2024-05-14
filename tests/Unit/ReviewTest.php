<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Role;
use App\Models\Score;
use App\Models\Submit;
use App\Models\User;
use App\Models\Viewpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ReviewTest extends TestCase
{
    // use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:refresh');
        Artisan::call('db:seed');
    }

    /**
     *
     */
    public function test_review(): void
    {
        $rev1 = User::factory()->create();
        $rev1->roles()->attach(4); // 4=reviewer
        $rev1->test_revconflict(); // Biddingをする
        // Reviewを作成する
        $revconfs = RevConflict::with('bidding')->where('user_id', $rev1->id)->get();
        foreach ($revconfs as $rv) {
            if ($rv->bidding_id > 3) {
                $paper = Paper::find($rv->paper_id);
                Review::firstOrCreate([
                    'submit_id' => $paper->submits->first()->id,
                    'paper_id' => $rv->paper_id,
                    'user_id' => $rev1->id,
                    'category_id' => $paper->category_id,
                    'ismeta' => false,
                ]);
            }
        }
        //つくったReviewについて、それぞれスコアを設定する
        $reviews = Review::where('user_id', $rev1->id)->get();
        foreach($reviews as $rv){
            $paper = Paper::find($rv->paper_id);
            $score_viewpointid = Viewpoint::where('category_id', $paper->category_id)->where("name","score")->first()->id;
            $scr = Score::firstOrCreate([
                'review_id' => $rv->id,
                'user_id' => $rev1->id,
                'viewpoint_id' => $score_viewpointid,
                'value' => 5+($paper->id % 3),
            ]);
        }

        $suball = Submit::where('score','!=', null)->get()->toArray();
        foreach($suball as $sub){
            $this->assertTrue($sub['score'] == 5+($sub['paper_id']%3));
            $this->assertTrue($sub['stddevscore'] == 0);
        }

        $rev2 = User::factory()->create();
        $rev2->roles()->attach(4); // 4=reviewer
        $rev2->test_revconflict(); // Biddingをする
        // Reviewを作成する
        $revconfs = RevConflict::with('bidding')->where('user_id', $rev2->id)->get();
        foreach ($revconfs as $rv) {
            if ($rv->bidding_id > 3) {
                $paper = Paper::find($rv->paper_id);
                Review::firstOrCreate([
                    'submit_id' => $paper->submits->first()->id,
                    'paper_id' => $rv->paper_id,
                    'user_id' => $rev2->id,
                    'category_id' => $paper->category_id,
                    'ismeta' => false,
                ]);
            }
        }
        //つくったReviewについて、それぞれスコアを設定する
        $reviews = Review::where('user_id', $rev2->id)->get();
        foreach($reviews as $rv){
            $paper = Paper::find($rv->paper_id);
            $score_viewpointid = Viewpoint::where('category_id', $paper->category_id)->where("name","score")->first()->id;
            $scr = Score::firstOrCreate([
                'review_id' => $rv->id,
                'user_id' => $rev2->id,
                'viewpoint_id' => $score_viewpointid,
                'value' => 4+($paper->id % 3),
            ]);
        }
        $suball = Submit::where('score','!=', null)->get()->toArray();
        foreach($suball as $sub){
            $this->assertTrue($sub['score'] == 4.5+($sub['paper_id']%3));
            // dump($sub['stddevscore']);
            $this->assertTrue($sub['stddevscore'] == 0.5);
        }
    }
}
