<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Tests\TestCase;

class PurgePdfThumbsTest extends TestCase
{
    public function test_admin_can_see_and_post_purge_pdf_thumbs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get(route('admin.purge_pdf_thumbs'))->assertStatus(403);

        $admin = User::find(1);
        $this->actingAs($admin);
        $response = $this->get(route('admin.purge_pdf_thumbs'));
        $response->assertStatus(200);
        $response->assertSee('PDFサムネイルキャッシュ');

        $response = $this->post(route('admin.purge_pdf_thumbs.post'));
        $response->assertRedirect(route('admin.purge_pdf_thumbs'));
        $response->assertSessionHas('feedback.success');

        $stats = File::pdfThumbCacheStats();
        $this->assertSame(0, $stats['count']);
    }
}
