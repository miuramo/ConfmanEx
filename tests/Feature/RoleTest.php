<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleTest extends TestCase
{
    protected static array $users = [];
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:refresh');
        Artisan::call('db:seed');

        foreach (\App\Models\Role::all() as $role) {
            $desc = $role->desc;
            $rolename = $role->name;
            $user = \App\Models\User::factory()->create([
                'name' => $desc . ' ' . $desc,
                'email' => $rolename . '@email.com',
                'password' => Hash::make($rolename),
            ]);

            $user->roles()->syncWithoutDetaching($role->id);
            self::$users[$rolename] = $user;
            // echo $rolename . "\n";
        }
    }

    /**
     * A basic test example.
     */
    public function test_admin_dashboard(): void
    {
        $u = User::find(1);
        $u->roles()->detach(1); // admin
        $u->roles()->detach(2); // manager
        $response = $this->actingAs($u)->get(route('role.top', ['role'=>'admin']));
        $response->assertStatus(403);

        // $u->roles()->syncWithoutDetaching(1); // admin
        $u->roles()->syncWithoutDetaching(2); // manager

        $response = $this->actingAs($u)->get(route('role.top', ['role'=>'admin']));
        $response->assertStatus(200);
    }

    public function test_other_roles(): void
    {
        foreach (self::$users as $rolename => $u) {
            $this->assertTrue($u->roles()->where("name", $rolename)->exists());
            foreach (self::$users as $rn2 => $u2) {
                if ($rn2 == $rolename) continue;
                $this->assertFalse($u->roles()->where("name", $rn2)->exists());
            }
        }
    }

    public function test_manager_role(): void
    {
        // var_dump(self::$users);
        $response = $this->actingAs(self::$users['manager'])->get(route('role.top', ['role'=>'admin']));
        $response->assertStatus(200);

        $response = $this->actingAs(self::$users['manager'])
            ->post(
                route('admin.disable_email'),
                ["invalid_email" => "test@gmail.com", "dryrun" => "DRYRUN"]
            );
        $response->assertStatus(302)
        ->assertRedirect(route('role.top', ['role'=>'admin']))
        ->assertSessionHas("feedback.success", "すべてのPaperの投稿連絡用メールアドレスから削除しました。");
    }
}
