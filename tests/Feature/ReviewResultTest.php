<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

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

        // if (count(self::$users) < 2) {
        //     for ($i = 0; $i < 6; $i++) {
        //         self::$users[] = User::factory()->create();
        //     }
        // }
        parent::paper_submit(1, 1, 1);
    }

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
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
