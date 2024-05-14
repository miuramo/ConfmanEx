<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Paper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class UserTest extends TestCase
{
    // use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:refresh');
        Artisan::call('db:seed');
    }

    /**
     * A basic test example.
     */
    public function test_create_a_user(): void
    {
        $user10 = User::factory()->create();
        $user11 = User::factory()->create();
        $user12 = User::factory()->create();
        $paper1 = Paper::create([
            'category_id' => 1,
            'contactemails' => $user11->email."\n",$user12->email,
            'owner' => $user10->id,
        ]);
        $paper1->updateContacts();

        $user20 = User::factory()->create();
        $user21 = User::factory()->create();
        $user22 = User::factory()->create();
        $paper2 = Paper::create([
            'category_id' => 2,
            'contactemails' => $user21->email."\n",$user22->email,
            'owner' => $user20->id,
        ]);
        $paper2->updateContacts();

        $this->assertTrue($paper1->owner == $user10->id);
        $this->assertTrue(Contact::where('email', $user20->email)->get() != null);
        $this->assertTrue(Contact::where('email', $user21->email)->get() != null);

        $this->assertTrue(Role::checkRoleUser("admin",1));
        $this->assertTrue(Role::checkRoleUser(1,1));
        $this->assertFalse(Role::checkRoleUser("admin",2));
        $this->assertTrue(Role::checkRoleUser("exe",1));

    }
}
