<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Accept;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewResultTest extends TestCase
{
    protected static array $users = [];
    protected function setUp(): void
    {
        parent::setUp();
        parent::proceed_to_submit(1);
        parent::proceed_to_assign(1);
        parent::proceed_to_decision(1);
    }



    public function test_authorpage_before_and_after_revreturn(): void
    {
        $combined = Accept::random_pids_for_each_accept(1);
        $show = $combined['show'];
        $showpid = $combined['showpid'];
        $metarev = $combined['metarev'];
        $rev = $combined['rev'];
        $accepts = $combined['accepts'];

        foreach ($show as $accid => $random_paper_owner) {
            // $accepts[$accid] 判定
            // $showpid[$accid] 論文ID
            // $random_paper_owner; 論文の著者
        }

        // ついでに、査読者も
        // $metarev[$accid]->user_id
        // $rev[$accid]->user_id
    }
}
