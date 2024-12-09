<?php

namespace Tests\Unit;

use App\Models\Category;
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

    public function test_paper_submit(): void
    {
        $num_paper_per_author = 2;
        $num_author = 5;
        parent::paper_submit(1, $num_author, $num_paper_per_author);
        $papers = Paper::get()->pluck('title', 'id')->toArray();
        // dump($papers);
        $authors = Paper::get()->pluck('owner', 'id')->toArray();
        // dump($authors);
        // $users = User::all();
        // dump($users);
        $this->assertTrue(Paper::where('category_id', 1)->count() == $num_paper_per_author * $num_author);

        // 査読者を追加
        $num_reviewer = 5;
        parent::add_reviewer($num_reviewer);
        // $reviewers = Role::findByIdOrName('reviewer')->users->pluck('name','id')->toArray();
        // dump(Role::findByIdOrName('reviewer')->users->count());
        $this->assertTrue(Role::findByIdOrName('reviewer')->users->count() == $num_reviewer + 1); // +1 for the first user
    }

    public function test_bidding(): void
    {
        $this->test_paper_submit();
        // parent::dump_papers();
        parent::dump_role('reviewer');
        parent::give_reviewer_priv_to_authors(1, 'reviewer');

        Review::extractAllCoAuthorRigais();
        parent::bidding('reviewer');
        dump(RevConflict::count());
        RevConflict::fillBidding(1, "reviewer", 5);
        parent::dump_conflict();
        // $this->assertCount(4, Role::findByIdOrName('reviewer')->users);

        parent::revassign(1, 'reviewer', 2);
        parent::revassign(1, 'metareviewer', 1);
        parent::dump_assign();
    }

    /**
     *
     */
    public function test_reviewtest(): void
    {
        // prepare some users and papers
        $user10 = User::factory()->create();
        $user11 = User::factory()->create();
        $user12 = User::factory()->create();
        $paper1 = Paper::create([
            'category_id' => 1,
            'contactemails' => $user11->email . "\n",
            $user12->email,
            'owner' => $user10->id,
        ]);
        $paper1->updateContacts();

        $user20 = User::factory()->create();
        $user21 = User::factory()->create();
        $user22 = User::factory()->create();
        $paper2 = Paper::create([
            'category_id' => 2,
            'contactemails' => $user21->email . "\n",
            $user22->email,
            'owner' => $user20->id,
        ]);
        $paper2->updateContacts();


        $rev1 = User::factory()->create();
        $rev1->roles()->syncWithoutDetaching(4); // 4=reviewer
        $rev1->test_revconflict(); // Biddingをする
        // ここまでがうまくいっているか確認する
        $this->assertTrue(RevConflict::where('user_id', $rev1->id)->count() == 2);

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
        foreach ($reviews as $rv) {
            $paper = Paper::find($rv->paper_id);
            $score_viewpointid = Viewpoint::where('category_id', $paper->category_id)->where("name", "score")->first()->id;
            $scr = Score::firstOrCreate([
                'review_id' => $rv->id,
                'user_id' => $rev1->id,
                'viewpoint_id' => $score_viewpointid,
                'value' => 5 + ($paper->id % 3),
            ]);
        }

        $suball = Submit::where('score', '!=', null)->get()->toArray();
        foreach ($suball as $sub) {
            $this->assertTrue($sub['score'] == 5 + ($sub['paper_id'] % 3));
            $this->assertTrue($sub['stddevscore'] == 0);
        }

        $rev2 = User::factory()->create();
        $rev2->roles()->syncWithoutDetaching(4); // 4=reviewer
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
        foreach ($reviews as $rv) {
            $paper = Paper::find($rv->paper_id);
            $score_viewpointid = Viewpoint::where('category_id', $paper->category_id)->where("name", "score")->first()->id;
            $scr = Score::firstOrCreate([
                'review_id' => $rv->id,
                'user_id' => $rev2->id,
                'viewpoint_id' => $score_viewpointid,
                'value' => 4 + ($paper->id % 3),
            ]);
        }
        $suball = Submit::where('score', '!=', null)->get()->toArray();
        foreach ($suball as $sub) {
            $this->assertTrue($sub['score'] == 4.5 + ($sub['paper_id'] % 3));
            // dump($sub['stddevscore']);
            $this->assertTrue($sub['stddevscore'] == 0.5);
        }
    }


    // public function test_adding_reviewer(): void
    // {        
    // }

    // public function test_reviewcomment_scoreonly_can_see_by_privileged_reviewers(){
    // list existing roles
    // $roles = Role::all()->pluck('name')->toArray();
    // $this->assertTrue(in_array('reviewer', $roles));
    // dump($roles);
    // use User factory to cretate author and reviewer and metareviewer and pc
    // $reviewer = User::factory()->withRoles('reviewer')->create();
    // $metareviewer = User::factory()->withRoles('metareviewer')->create();
    // $pc = User::factory()->withRoles('pc')->create();
    // $author = User::factory()->withPapers(2,1)->create();

    // show categories settings 
    // $cats = Category::all()->pluck('name')->toArray();
    // dump($cats);        
    // }
    public function test_just_test(): void
    {
        $this->assertTrue(true);
    }
}
