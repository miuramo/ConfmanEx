<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    protected static array $users = [];
    protected function setUp(): void
    {
        parent::setUp();

        if (count(self::$users) < 2) {
            // 変数を初期化する
            for ($i = 0; $i < 6; $i++) {
                self::$users[] = User::factory()->create();
                // echo self::$users[$i]->id . "\n";
            }
        }
    }

    public function admin_can_see_admin_crud()
    {
        // echo "*********** test1\n";
        $user = self::$users[0];
        $this->assertDatabaseHas('users', ['email' => $user->email]);
        // $role = Role::factory()->create(['name' => 'writer']);
        // $user->roles()->syncWithoutDetaching($role);
        $this->actingAs($user);
        $response = $this->get(route('admin.crud'));
        $response->assertStatus(403);

        $admin = User::find(1);
        $this->actingAs($admin);
        $response = $this->get(route('admin.crud'));
        $response->assertStatus(200);

    }

    public function admin_can_post_admin_crudpost()
    {
        // echo "*********** test2\n";

        $admin = User::find(1);
        $this->actingAs($admin);
        $response = $this->post(route('admin.crudpost'), [
            'field' => "name",
            'data_id' => 1,
            'table' => "users",
            'val' => "Admin User",
            'tdid' => "name__1__papers",
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', ['name' => "Admin User"]);

    }

}
