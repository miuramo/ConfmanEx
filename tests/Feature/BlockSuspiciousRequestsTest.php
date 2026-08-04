<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BlockSuspiciousRequestsTest extends TestCase
{
    public function test_suspicious_request_is_blocked(): void
    {
        Route::get('/test-block', function () {
            return 'ok';
        });

        $response = $this->get('/test-block?payload=<?php eval(base64_decode("x"));');

        $response->assertStatus(403);
    }
}
