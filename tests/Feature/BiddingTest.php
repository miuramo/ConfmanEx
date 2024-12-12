<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BiddingTest extends TestCase
{
    protected static array $users = [];
    protected function setUp(): void
    {
        parent::setUp();
        parent::paper_submit(1, 1, 1);
    }

    public function test_the_reviewer_can_see_bidding_page(): void
    {
        $reviewer = User::factory()->withRoles('reviewer')->create();
        $this->actingAs($reviewer);
        $resp = $this->get('/profile');
        $resp->assertStatus(200);
        $resp->assertSee('登録情報の修正');

        // $resp = $this->get('/role/reviewer/top');
        $resp = $this->get('/paper');
        $resp->assertStatus(200);
        $resp->assertSee("投稿一覧");

        // Bidding開始してない
        parent::start_bidding(1,false);
        parent::end_bidding(1,false);
        $resp = $this->get('/role/reviewer/top');
        $resp->assertDontSee("利害表明 (登壇発表)");
        $resp = $this->get('/review/conflict/1');
        $resp->assertStatus(403);

        // Bidding開始したら見れる
        parent::start_bidding(1,true);
        parent::end_bidding(1,false);
        $resp = $this->get('/');
        $resp->assertStatus(200);
        $resp->assertSee("査読");

        $resp = $this->get('/role/reviewer/top');
        $resp->assertStatus(200);
        $resp->assertSee("/review/conflict/1");
        $resp->assertSee("利害表明 (登壇発表)");
        
        // Bidding終了したら見れない
        parent::start_bidding(1,true);
        parent::end_bidding(1,true);
        $resp = $this->get('/role/reviewer/top');
        $resp->assertDontSee("利害表明 (登壇発表)");
        $resp = $this->get('/review/conflict/1');
        $resp->assertStatus(403);

        // 開始してないけど終了している→見れない
        parent::start_bidding(1,false);
        parent::end_bidding(1,true);
        $resp = $this->get('/role/reviewer/top');
        $resp->assertDontSee("利害表明 (登壇発表)");
        $resp = $this->get('/review/conflict/1');
        $resp->assertStatus(403);
    }



    public function test_reviewcomment_scoreonly_can_see_by_privileged_reviewers(): void
    {
        $resp = $this->get('/');
        $resp->assertStatus(200);

        $reviewer = User::factory()->withRoles('reviewer')->create();
        $metareviewer = User::factory()->withRoles('metareviewer')->create();
        $pc = User::factory()->withRoles('pc')->create();
        $author = User::factory()->withPapers(2, 1)->create();

        $cat1 = Category::find(1);
        $cat1->status__revlist_on = 1;
        $cat1->status__revlist_for = 'reviewer';
        $cat1->save();
    }
}
