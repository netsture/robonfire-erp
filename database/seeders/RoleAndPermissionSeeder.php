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

        // Roles Definition
        $rolesData = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full administrative access to all modules and system settings.',
                'perms' => array_keys($permissionModels)
            ],
            [
                'name' => 'Store Manager',
                'slug' => 'store-manager',
                'description' => 'Manages inventory, suppliers, purchases, products and sales.',
                'perms' => ['products.view', 'products.create', 'products.edit', 'customers.view', 'suppliers.view', 'suppliers.create', 'purchases.view', 'purchases.create', 'sales.view', 'sales.create', 'reports.view']
            ],
            [
                'name' => 'Sales Executive',
                'slug' => 'sales-executive',
                'description' => 'Handles POS sales, customer management and inventory view.',
                'perms' => ['customers.view', 'customers.create', 'products.view', 'sales.view', 'sales.create']
            ],
            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'Access to sales ledgers, purchase receipts and financial reporting.',
                'perms' => ['sales.view', 'purchases.view', 'customers.view', 'suppliers.view', 'reports.view']
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
