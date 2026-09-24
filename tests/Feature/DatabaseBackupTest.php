<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_download_database()
    {
        $response = $this->get(route('database.download'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_download_database()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('database.download'));
        $response->assertStatus(403);
    }

    public function test_admin_can_download_full_database_sql_dump()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('database.download'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/x-sql', $response->headers->get('content-type'));
        $this->assertStringContainsString('filename=ERP_Database_Dump_', $response->headers->get('content-disposition'));
    }
}
