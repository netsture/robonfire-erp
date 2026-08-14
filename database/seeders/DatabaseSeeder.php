<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Default Admin Account
        // $admin = User::updateOrCreate(
        //     ['email' => 'superadmin@gmail.com'],
        //     [
        //         'name'     => 'System Administrator',
        //         'password' => Hash::make('123456'),
        //         'phone'    => '+1 (555) 019-2834',
        //         'role'     => 'superadmin',
        //         'firm_id' => null,
        //         'status' => 'active',
        //     ]
        // );

        $this->call([
            RoleAndPermissionSeeder::class,
            DemoERPSeeder::class,
        ]);
    }
}
