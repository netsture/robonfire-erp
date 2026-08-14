<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionsByModule = [
            'Users' => [
                'users.view'   => 'View Users List',
                'users.create' => 'Create New Users',
                'users.edit'   => 'Edit Existing Users',
                'users.delete' => 'Delete Users',
            ],
            'Roles' => [
                'roles.view'   => 'View Roles & Permissions',
                'roles.manage' => 'Manage Roles & Assign Permissions',
            ],
            'Customers' => [
                'customers.view'   => 'View Customers List & Ledger',
                'customers.create' => 'Create New Customers',
                'customers.edit'   => 'Edit Customer Details',
                'customers.delete' => 'Delete Customers',
            ],
            'Suppliers' => [
                'suppliers.view'   => 'View Suppliers List & Balance',
                'suppliers.create' => 'Create New Suppliers',
                'suppliers.edit'   => 'Edit Supplier Details',
                'suppliers.delete' => 'Delete Suppliers',
            ],
            'Products' => [
                'products.view'   => 'View Product Catalog & Inventory',
                'products.create' => 'Create New Products & Categories',
                'products.edit'   => 'Edit Product Details & Stock',
                'products.delete' => 'Delete Products',
            ],
            'Sales' => [
                'sales.view'   => 'View Sales Orders & Invoices',
                'sales.create' => 'Create & Process Sales Orders',
                'sales.delete' => 'Cancel / Delete Sales Orders',
            ],
            'Purchases' => [
                'purchases.view'   => 'View Purchase Orders & Receipts',
                'purchases.create' => 'Record New Purchase Orders',
                'purchases.delete' => 'Delete Purchase Records',
            ],
            'Reports' => [
                'reports.view' => 'Access Financial & Operational Reports',
            ],
        ];

        $permissionModels = [];
        foreach ($permissionsByModule as $module => $perms) {
            foreach ($perms as $slug => $name) {
                $permissionModels[$slug] = Permission::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'module' => $module, 'description' => "Permission to {$name}"]
                );
            }
        }

        // Roles Definition (Strictly 3 roles)
        $rolesData = [
            [
                'name' => 'Superadmin',
                'slug' => 'superadmin',
                'description' => 'Global system administrator. Manages all tenants, global configurations, and cross-tenant data.',
                'perms' => array_keys($permissionModels)
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Tenant administrator. Full operational and administrative access within assigned tenant firm.',
                'perms' => array_keys($permissionModels)
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Standard tenant user. Standard operational access for day-to-day sales, purchases, and catalog viewing.',
                'perms' => ['products.view', 'customers.view', 'suppliers.view', 'sales.view', 'sales.create', 'purchases.view', 'purchases.create']
            ]
        ];

        foreach ($rolesData as $rData) {
            $role = Role::firstOrCreate(
                ['slug' => $rData['slug']],
                ['name' => $rData['name'], 'description' => $rData['description']]
            );

            $permIds = Permission::whereIn('slug', $rData['perms'])->pluck('id');
            $role->permissions()->sync($permIds);
        }

        // Assign Admin Role to Default User
        $adminUser = User::where('email', 'admin@erp.com')->first();
        if ($adminUser) {
            $adminRole = Role::where('slug', 'admin')->first();
            if ($adminRole) {
                $adminUser->roles()->sync([$adminRole->id]);
            }
        }
    }
}
