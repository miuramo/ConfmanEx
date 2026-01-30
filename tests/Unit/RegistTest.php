<?php

namespace Tests\Unit;

use App\Models\Regist;
use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;
use Mockery;

class RegistTest extends TestCase
{
    // protected function tearDown(): void
    // {
    //     Mockery::close();
    //     parent::tearDown();
    // }

    /**
     * token test
     */
    public function test_it_generates_a_token()
    {
        $user10 = User::factory()->create();

        $regist = new Regist([
            'id' => 1,
            'user_id' => $user10->id,
        ]);

        $expectedToken = sha1($regist->id . $user10->id . $regist->created_at);
        $this->assertEquals($expectedToken, $regist->token());

    }

}
