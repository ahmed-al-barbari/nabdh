<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class RoleSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/admin/users'); // adjust route as appropriate
        $response->assertStatus(403);
    }
}
