<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ContactTest extends TestCase
{
    // use RefreshDatabase;
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
        // Artisan::call('db:seed');
    }

    /**
     * A basic test example.
     */
    public function test_create_a_contact(): void
    {
        $user10 = User::factory()->create();
        $user11 = User::factory()->create();
        $user12 = User::factory()->create();
        $paper1 = Paper::create([
            'category_id' => 1,
            'contactemails' => $user11->email."\n".$user12->email,
            'owner' => $user10->id,
        ]);
        $paper1->updateContacts();

        $user20 = User::factory()->create();
        $user21 = User::factory()->create();
        $user22 = User::factory()->create();
        $paper2 = Paper::create([
            'category_id' => 2,
            'contactemails' => $user21->email."\n".$user22->email,
            'owner' => $user20->id,
        ]);
        $paper2->updateContacts();

        $this->assertTrue($paper1->owner == $user10->id);
        $this->assertTrue(Contact::where('email', $user20->email)->first() != null);
        $this->assertTrue(Contact::where('email', $user21->email)->first() != null);

        $this->assertTrue($paper1->isCoAuthorEmail($user12->email));

        $paper1->add_contactemail($user21->email);
        $this->assertTrue($paper1->isCoAuthorEmail($user21->email));
        $this->assertTrue($paper1->isCoAuthorEmail($user11->email));

        $paper1->remove_contact(Contact::where('email', $user21->email)->first());
        $this->assertFalse($paper1->isCoAuthorEmail($user21->email));
    }
}
