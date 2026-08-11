<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantService
{
    /**
     * Create a new tenant and its initial administrator.
     */
    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Tenant
            $tenant = Tenant::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'status' => $data['status'] ?? 'Active',
            ]);

            // 2. Create Tenant Admin User
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'] . ' Admin',
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'status' => $data['status'] ?? 'Active',
            ]);

            // 3. Assign Admin Role
            $user->assignRole('Admin');

            return $tenant;
        });
    }

    /**
     * Update an existing tenant and sync status to its admin users.
     */
    public function updateTenant(Tenant $tenant, array $data): Tenant
    {
        return DB::transaction(function () use ($tenant, $data) {
            $tenant->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'status' => $data['status'],
            ]);

            // Sync status to the tenant's admin users
            User::where('tenant_id', $tenant->id)->update([
                'status' => $data['status']
            ]);

            return $tenant;
        });
    }
}
