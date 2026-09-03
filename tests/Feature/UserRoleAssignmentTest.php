<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Firm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected $firm;
    protected $superadminRole;
    protected $adminRole;
    protected $userRole;
    protected $superadminUser;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firm = Firm::create([
            'name' => 'Test Firm',
            'slug' => 'test-firm',
            'email' => 'firm@example.com',
            'phone' => '9876543210',
            'address' => '123 Test Street',
            'status' => 'active',
        ]);

        $this->superadminRole = Role::create(['name' => 'Superadmin', 'slug' => 'superadmin']);
        $this->adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->userRole = Role::create(['name' => 'User', 'slug' => 'user']);

        $this->superadminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'phone' => '9876543200',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'firm_id' => null,
            'status' => 'active',
        ]);

        $this->adminUser = User::create([
            'name' => 'Tenant Admin',
            'email' => 'admin@example.com',
            'phone' => '9876543201',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'firm_id' => $this->firm->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_create_user_page_shows_only_user_role()
    {
        $response = $this->actingAs($this->adminUser)->get('/users/create');

        $response->assertStatus(200);
        $response->assertViewHas('roles', function ($roles) {
            return $roles->count() === 1 && $roles->first()->slug === 'user';
        });
    }

    public function test_admin_can_create_user_with_user_role()
    {
        $response = $this->actingAs($this->adminUser)->post('/users', [
            'name' => 'New Staff',
            'email' => 'staff@example.com',
            'phone' => '9876543202',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->userRole->id,
            'status' => 'active',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.com',
            'role' => 'User',
        ]);
    }

    public function test_admin_cannot_assign_admin_role()
    {
        $response = $this->actingAs($this->adminUser)->post('/users', [
            'name' => 'Unauthorized Admin',
            'email' => 'unauthadmin@example.com',
            'phone' => '9876543203',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->adminRole->id,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['role_id']);
        $this->assertDatabaseMissing('users', [
            'email' => 'unauthadmin@example.com',
        ]);
    }

    public function test_superadmin_cannot_create_user_due_to_view_only_restriction()
    {
        $response = $this->actingAs($this->superadminUser)->post('/users', [
            'name' => 'New Tenant Admin',
            'email' => 'newadmin@example.com',
            'phone' => '9876543204',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->adminRole->id,
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }
}
