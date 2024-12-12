<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        parent::proceed_to_submit(1);
        parent::proceed_to_assign(1);
    }

    public function test_reviewing() : void
    {
        $up = Review::arr_up_status(1);
        $up_revobj = Review::arr_up_rev(1);

        // 配列$upの最初のキーを取得
        $revuid = array_key_first($up);
        // dump($revuid);
        $rev = User::find($revuid);
        $this->actingAs($rev);

        parent::start_review(1, false);
        parent::end_review(1, false);
        $resp = $this->get('/role/reviewer/top');
        $resp->assertSee("または査読開始前です");
        $resp = $this->get('/review'); // 担当査読一覧
        $resp->assertStatus(200);
        foreach($up[$revuid] as $pid => $status) {
            $resp->assertDontSee(sprintf("PaperID : %03d", $pid));
        }
        foreach($up_revobj[$revuid] as $pid => $revobj){
            $resp = $this->get("/review/{$revobj->id}/edit");
            $resp->assertStatus(403); //まだ査読開始前
        }

        parent::start_review(1, true);
        parent::end_review(1, false);
        $resp = $this->get('/role/reviewer/top');
        $resp->assertDontSee("または査読開始前です");
        $resp->assertSee('登壇発表のみの一覧');
        $resp = $this->get('/review'); // 担当査読一覧
        $resp->assertStatus(200);
        foreach($up[$revuid] as $pid => $status) {
            $resp->assertSee(sprintf("PaperID : %03d", $pid));
        }
        foreach($up_revobj[$revuid] as $pid => $revobj){
            $resp = $this->get("/review/{$revobj->id}/edit");
            $resp->assertStatus(200);
            $resp->assertSee('査読（編集）');
        }

        parent::start_review(1, true);
        parent::end_review(1, true);
        $resp = $this->get('/role/reviewer/top');
        $resp->assertSee('登壇発表のみの一覧');
        $resp = $this->get('/review'); // 担当査読一覧
        $resp->assertStatus(200);
        foreach($up[$revuid] as $pid => $status) {
            $resp->assertSee(sprintf("PaperID : %03d", $pid));
        }
        foreach($up_revobj[$revuid] as $pid => $revobj){
            $resp = $this->get("/review/{$revobj->id}");
            $resp->assertStatus(200);
            $resp->assertSee('査読（参照）');
        }
    }

}
