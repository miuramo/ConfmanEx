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
        $response0 = $this->get('/profile');
        $response0->assertStatus(200);
        $response0->assertSee('登録情報の修正');

        // $response = $this->get('/role/reviewer/top');
        $response = $this->get('/paper');
        $response->assertStatus(200);
        $response->assertSee("投稿一覧");
        // $response->assertDontSee("利害表明 (登壇発表)");
        // Bidding開始
        parent::start_bidding(1,true);
        $response2 = $this->get('/');
        $response2->assertStatus(200);
        $response2->assertSee("査読");

        // NAME_OF_META があるか？
        // $nameofmeta = Setting::findByIdOrName('NAME_OF_META');
        // dump($nameofmeta);
        $response3 = $this->get('/role/reviewer/top');
        // $response2->dumpHeaders();
        $response3->assertStatus(200);
        $response3->assertSee("/review/conflict/1");
        $response3->assertSee("利害表明 (登壇発表)");
        
        // $response = $this->get(route('role.top', ['role' => 'reviewer']));

        // dump($response->getContent()); // レスポンスボディをダンプ
        // $response->assertStatus(200);
    }



    public function test_reviewcomment_scoreonly_can_see_by_privileged_reviewers(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

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
