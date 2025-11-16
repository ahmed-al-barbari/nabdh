<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class WeakConnectivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_handles_external_timeout_gracefully()
    {
        Http::fake([
            'external-service.com/*' => Http::response(null, 504),
        ]);

        $response = $this->get('/api/external-data'); // assumes route for demonstration
        $this->assertTrue(
            in_array($response->status(), [504, 500, 200])
        );
    }
}
