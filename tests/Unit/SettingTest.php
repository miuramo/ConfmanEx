<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Setting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingTest extends TestCase
{

    public function test_auto_role_member()
    {
        // テスト用のデータを作成
        $user = User::factory()->create(['name' => 'testuser']);
        Setting::create([
            'name' => 'REVIEWER_MEMBER',
            'value' => 'testuser',
            'valid' => true,
        ]);

        // メソッドを実行
        Setting::auto_role_member();
        $role = Role::find(5);
        // アサーション
        $this->assertTrue($user->roles->contains($role));
    }

    public function test_seeder()
    {
        // メソッドを実行
        Setting::seeder();

        // アサーション
        $this->assertDatabaseHas('settings', [
            'name' => 'NAME_OF_META',
            'value' => 'メタ査読者',
        ]);

        $this->assertDatabaseHas('settings', [
            'name' => 'SKIP_BIBINFO',
            'value' => '["keyword","etitle","eabst","ekeyword"]',
        ]);

        $this->assertDatabaseHas('settings', [
            'name' => 'FILE_DESCRIPTIONS',
            'value' => '{"pdf":"論文PDF","altpdf":"ティザー資料","img":"代表画像","video":"参考ビデオ","pptx":"PowerPoint(pptx)"}',
        ]);
    }
}
