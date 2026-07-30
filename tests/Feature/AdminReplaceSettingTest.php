<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReplaceSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_replace_setting_page_and_run_bulk_replace(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->syncWithoutDetaching([1]);

        Setting::setval('CONFTITLE_YEAR', '2026');
        Setting::setval('FILEPUT_DIR', 'z2026');
        Setting::setval('INTRO_VIDEO_URL', 'https://example.com/2026/video');

        $this->actingAs($admin)
            ->get(route('admin.replace_setting'))
            ->assertOk()
            ->assertSee('設定の一括置換');

        $this->actingAs($admin)
            ->post(route('admin.replace_setting'), [
                'pre' => '2026',
                'post' => '2027',
            ])
            ->assertRedirect(route('admin.replace_setting'));

        $this->assertSame('2027', Setting::getval('CONFTITLE_YEAR'));
        $this->assertSame('z2027', Setting::getval('FILEPUT_DIR'));
        $this->assertSame('https://example.com/2027/video', Setting::getval('INTRO_VIDEO_URL'));
    }
}
