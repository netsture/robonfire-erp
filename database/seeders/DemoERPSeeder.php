<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoERPSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $superadminRole = Role::where('slug', 'superadmin')->first();

        // 2. Superadmin User
        $superadminUser = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('123456'),
                'phone' => '9876543210',
                'role' => 'superadmin',
                'firm_id' => null,
                'status' => 'active',
            ]
        );

        if ($superadminRole) {
            $superadminUser->roles()->sync([$superadminRole->id]);
        }
    }
}