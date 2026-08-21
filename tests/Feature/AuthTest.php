<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_login_with_email()
    {
        $user = User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@example.com',
            'phone'    => '9876543210',
            'password' => Hash::make('password123'),
            'role'     => 'superadmin',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => 'superadmin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_superadmin_can_login_with_phone()
    {
        $user = User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@example.com',
            'phone'    => '9876543210',
            'password' => Hash::make('password123'),
            'role'     => 'superadmin',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => '9876543210',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_with_email()
    {
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'phone'    => '9876543211',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_with_phone()
    {
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'phone'    => '9876543211',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => '9876543211',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_standard_user_can_login_with_email()
    {
        $user = User::create([
            'name'     => 'Standard User',
            'email'    => 'user@example.com',
            'phone'    => '9876543212',
            'password' => Hash::make('password123'),
            'role'     => 'user',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_standard_user_can_login_with_phone()
    {
        $user = User::create([
            'name'     => 'Standard User',
            'email'    => 'user@example.com',
            'phone'    => '9876543212',
            'password' => Hash::make('password123'),
            'role'     => 'user',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => '9876543212',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_formatted_phone_number()
    {
        $user = User::create([
            'name'     => 'Formatted Phone User',
            'email'    => 'formatted@example.com',
            'phone'    => '9876543215',
            'password' => Hash::make('password123'),
            'role'     => 'user',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => '+91 98765 43215',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'phone'    => '9876543213',
            'password' => Hash::make('correctpassword'),
            'role'     => 'user',
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'login'    => '9876543213',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_login()
    {
        User::create([
            'name'     => 'Deactivated User',
            'email'    => 'deactive@example.com',
            'phone'    => '9876543214',
            'password' => Hash::make('password123'),
            'role'     => 'user',
            'status'   => 'inactive',
        ]);

        $response = $this->post('/login', [
            'login'    => '9876543214',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
